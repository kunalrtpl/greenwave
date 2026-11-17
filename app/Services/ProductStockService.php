<?php

namespace App\Services;

use App\ProductStock;
use App\ProductStockLedger;
use App\User;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * The only way product_stocks is allowed to change.
 *
 * Every call locks the product's row, moves one bucket, rewrites total_stock
 * and writes a credit / debit line in product_stock_ledgers describing what
 * happened, which document it belongs to and who did it. Balance and ledger
 * therefore can never drift apart.
 *
 *   ProductStockService::credit($productId, 500, [
 *       'bucket'    => ProductStock::BUCKET_INCOMING,
 *       'source'    => ProductStockService::SOURCE_INCOMING_MATERIAL,
 *       'reference' => $productInventory,
 *       'batch_no'  => '1002932920',
 *       'notes'     => $request->remarks,
 *   ]);
 */
class ProductStockService
{
    const SOURCE_INCOMING_MATERIAL = 'incoming_material';
    const SOURCE_PRODUCTION        = 'production';
    const SOURCE_ADJUSTMENT        = 'adjustment';
    const SOURCE_SAMPLE_DISPATCH   = 'sample_dispatch';
    const SOURCE_OPENING           = 'opening_balance';

    /** Screen / action each source stands for, used in the auto remark. */
    public static function sources()
    {
        return [
            self::SOURCE_INCOMING_MATERIAL => 'Add Incoming Material',
            self::SOURCE_PRODUCTION        => 'Production',
            self::SOURCE_ADJUSTMENT        => 'Stock Adjustment',
            self::SOURCE_SAMPLE_DISPATCH   => 'Sample Dispatch',
            self::SOURCE_OPENING           => 'Opening Balance',
        ];
    }

    /** Stock coming in. Returns the ledger line that was written. */
    public static function credit($productId, $qty, array $options = [])
    {
        return self::record(ProductStockLedger::ENTRY_CREDIT, $productId, $qty, $options);
    }

    /** Stock going out. Returns the ledger line that was written. */
    public static function debit($productId, $qty, array $options = [])
    {
        return self::record(ProductStockLedger::ENTRY_DEBIT, $productId, $qty, $options);
    }

    /**
     * Reverses an earlier line — same bucket and qty, the other way round.
     * Used when a document that added stock is cancelled or corrected.
     */
    public static function reverse(ProductStockLedger $line, array $options = [])
    {
        $opposite = $line->is_credit ? ProductStockLedger::ENTRY_DEBIT : ProductStockLedger::ENTRY_CREDIT;

        $options = array_merge([
            'bucket'       => $line->stock_type,
            'source'       => $line->source,
            'source_label' => $line->source_label,
            'reference_type' => $line->reference_type,
            'reference_id'   => $line->reference_id,
            'reference_no' => $line->reference_no,
            'batch_no'     => $line->batch_no,
            'reason'       => 'reversal of ledger #' . $line->id,
        ], $options);

        return self::record($opposite, $line->product_id, $line->qty, $options);
    }

    /** Current balance row for a product (created at zero if it has none yet). */
    public static function balance($productId)
    {
        return ProductStock::forProduct($productId);
    }

    private static function record($entryType, $productId, $qty, array $options)
    {
        $qty = round((float) $qty, 3);

        if (!$productId) {
            throw new Exception('A product is required to move stock.');
        }

        if ($qty <= 0) {
            throw new Exception('Stock movement qty must be greater than zero.');
        }

        $bucket = isset($options['bucket']) ? $options['bucket'] : ProductStock::BUCKET_INCOMING;
        $column = ProductStock::columnFor($bucket);

        if (!$column) {
            throw new Exception('Unknown stock type "' . $bucket . '".');
        }

        return DB::transaction(function () use ($entryType, $productId, $qty, $options, $bucket, $column) {

            // Make sure the row exists before locking it.
            ProductStock::forProduct($productId);

            $stock = ProductStock::where('product_id', $productId)->lockForUpdate()->first();

            $stockBefore = round((float) $stock->{$column}, 3);
            $totalBefore = round((float) $stock->total_stock, 3);

            $isCredit = $entryType === ProductStockLedger::ENTRY_CREDIT;
            $stockAfter = round($isCredit ? $stockBefore + $qty : $stockBefore - $qty, 3);

            if (!$isCredit && $stockAfter < 0 && empty($options['allow_negative'])) {
                throw new Exception(
                    'Not enough ' . array_get(ProductStock::buckets(), $bucket, $bucket) . ' — '
                    . ProductStockLedger::formatQty($stockBefore) . ' Kg available, '
                    . ProductStockLedger::formatQty($qty) . ' Kg needed.'
                );
            }

            $stock->{$column} = $stockAfter;
            $stock->recalculateTotal()->save();

            $user = self::resolveUser($options);

            return ProductStockLedger::create([
                'product_id'       => $productId,
                'product_stock_id' => $stock->id,
                'entry_type'       => $entryType,
                'stock_type'       => $bucket,
                'qty'              => $qty,
                'stock_before'     => $stockBefore,
                'stock_after'      => $stockAfter,
                'total_before'     => $totalBefore,
                'total_after'      => round((float) $stock->total_stock, 3),
                'source'           => array_get($options, 'source'),
                'source_label'     => self::sourceLabel($options),
                'reference_type'   => self::referenceType($options),
                'reference_id'     => self::referenceId($options),
                'reference_no'     => array_get($options, 'reference_no'),
                'batch_no'         => array_get($options, 'batch_no'),
                'remarks'          => self::remark($entryType, $qty, $bucket, $options, $user),
                'notes'            => array_get($options, 'notes'),
                'created_by'       => $user ? $user->id : null,
                'created_by_name'  => $user ? $user->name : 'System',
            ]);
        });
    }

    /**
     * The auto remark: what moved, where it came from, which document it
     * belongs to and who did it — written once, here, so every screen that
     * moves stock reads the same way.
     */
    private static function remark($entryType, $qty, $bucket, array $options, $user)
    {
        if (!empty($options['remarks'])) {
            return $options['remarks'];
        }

        $parts = [
            ($entryType === ProductStockLedger::ENTRY_CREDIT ? 'Credited ' : 'Debited ')
                . ProductStockLedger::formatQty($qty) . ' Kg '
                . ($entryType === ProductStockLedger::ENTRY_CREDIT ? 'to ' : 'from ')
                . array_get(ProductStock::buckets(), $bucket, $bucket),
        ];

        if ($label = self::sourceLabel($options)) {
            $parts[] = 'via ' . $label;
        }

        if ($ref = array_get($options, 'reference_no')) {
            $parts[] = 'Ref ' . $ref;
        }

        if ($batch = array_get($options, 'batch_no')) {
            $parts[] = 'Batch ' . $batch;
        }

        if ($reason = array_get($options, 'reason')) {
            $parts[] = $reason;
        }

        $parts[] = 'by ' . ($user ? $user->name : 'System');

        return implode(' · ', $parts);
    }

    private static function sourceLabel(array $options)
    {
        if (!empty($options['source_label'])) {
            return $options['source_label'];
        }

        return array_get(self::sources(), array_get($options, 'source'));
    }

    private static function referenceType(array $options)
    {
        if (!empty($options['reference_type'])) {
            return $options['reference_type'];
        }

        $reference = array_get($options, 'reference');

        return $reference instanceof Model ? get_class($reference) : null;
    }

    private static function referenceId(array $options)
    {
        if (!empty($options['reference_id'])) {
            return $options['reference_id'];
        }

        $reference = array_get($options, 'reference');

        return $reference instanceof Model ? $reference->getKey() : null;
    }

    /** Whoever is signed in, or whoever the caller named (jobs, commands). */
    private static function resolveUser(array $options)
    {
        $user = array_get($options, 'user');

        if ($user instanceof User) {
            return $user;
        }

        if ($id = ($user ?: array_get($options, 'user_id'))) {
            return User::find($id);
        }

        return Auth::check() ? Auth::user() : null;
    }
}
