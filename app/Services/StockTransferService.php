<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\Services\EmployeeBatchStockService;
use App\Services\ProductBatchService;
use App\StockTransfer;
use App\StockTransferItem;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Free-sample stock moving between two marketing employees.
 *
 * The whole module turns on one rule: **the qty leaves the sender's usable
 * stock the moment the request is raised.** It goes into blocked_stock, where
 * it cannot be transferred again, dispatched or counted as in hand, and it
 * only leaves there when the transfer is settled:
 *
 *   raise     stock_in_hand → blocked_stock   (sender)
 *   approve   blocked_stock → gone            (sender)
 *             → stock_in_hand                 (receiver)
 *   decline   blocked_stock → stock_in_hand   (sender)
 *   cancel    blocked_stock → stock_in_hand   (sender)
 *
 * Every one of those happens inside a transaction with the stock rows locked,
 * so two requests racing for the same stock cannot both succeed. Emails are
 * sent after the transaction commits: a bounced email must never undo a
 * transfer.
 */
class StockTransferService
{
    const EVENT_REQUESTED = 'stock_transfer_requested_employee';
    const EVENT_DECIDED   = 'stock_transfer_decided_employee';

    /**
     * Raise a transfer and block the stock behind it.
     *
     * @param  array $data  to_user_id, items[{product_id, qty}], remarks, transfer_date
     * @param  int   $fromUserId the employee giving the stock
     * @throws ValidationException
     */
    public function create(array $data, $fromUserId)
    {
        $this->assertMarketingEmployee($fromUserId, 'from_user_id',
            'Only marketing employees can transfer sample stock.');
        $this->assertMarketingEmployee($data['to_user_id'], 'to_user_id',
            'Stock can only be transferred to a marketing employee.');

        if ((int) $data['to_user_id'] === (int) $fromUserId) {
            throw ValidationException::withMessages([
                'to_user_id' => 'You cannot transfer stock to yourself.',
            ]);
        }

        $transfer = DB::transaction(function () use ($data, $fromUserId) {
            $number = $this->nextNumber();

            $transfer = StockTransfer::create([
                'transfer_no'   => $number,
                'reference_no'  => 'ST-' . $number,
                'from_user_id'  => $fromUserId,
                'to_user_id'    => $data['to_user_id'],
                'status'        => StockTransfer::STATUS_PENDING,
                'transfer_date' => isset($data['transfer_date']) ? $data['transfer_date'] : date('Y-m-d'),
                'remarks'       => isset($data['remarks']) ? $data['remarks'] : null,
            ]);

            $rows = $this->normaliseItems($data['items']);

            // They may not send more of a batch than they are holding of it.
            $this->assertBatchStock($fromUserId, $rows);

            // Blocking first, and per product rather than per row: two rows of
            // one product out of two batches are still one claim on what the
            // sender holds. If they do not hold it, nothing is created at all,
            // because the whole thing is one transaction.
            foreach ($this->totalsByProduct($rows) as $productId => $qty) {
                $this->blockStock($fromUserId, $productId, $qty);
            }

            foreach ($rows as $row) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $row['product_id'],
                    'batch_ref'         => $row['batch']->ref(),
                    'batch_no'          => $row['batch']->batchNo,
                    'qty'               => $row['qty'],
                ]);
            }

            return $transfer;
        });

        $transfer->load(['items.product', 'from_user', 'to_user']);

        // Outside the transaction: a bounced email must never undo the block.
        $this->notifyReceiver($transfer);

        return $transfer;
    }

    /**
     * The receiving employee accepts it: the blocked qty becomes their stock.
     *
     * @throws ValidationException
     */
    public function approve(StockTransfer $transfer, $userId)
    {
        $this->assertDecidable($transfer, $userId);

        DB::transaction(function () use ($transfer, $userId) {
            foreach ($transfer->items as $item) {
                // The sender gives it up …
                $this->releaseBlocked($transfer->from_user_id, $item->product_id, $item->qty, false);
                // … and the receiver now holds it.
                $this->creditStock($userId, $item->product_id, $item->qty);
            }

            $transfer->status      = StockTransfer::STATUS_APPROVED;
            $transfer->approved_at = now();
            $transfer->save();
        });

        $transfer->load(['items.product', 'from_user', 'to_user']);

        $this->notifySender($transfer);

        return $transfer;
    }

    /**
     * The receiving employee refuses it: the blocked qty goes back.
     *
     * @throws ValidationException
     */
    public function decline(StockTransfer $transfer, $userId, array $data)
    {
        $this->assertDecidable($transfer, $userId);

        if (empty(trim((string) (isset($data['decline_reason']) ? $data['decline_reason'] : '')))) {
            throw ValidationException::withMessages([
                'decline_reason' => 'Please give a reason for refusing the stock.',
            ]);
        }

        DB::transaction(function () use ($transfer, $data) {
            $this->returnEverythingToSender($transfer);

            $transfer->status         = StockTransfer::STATUS_DECLINED;
            $transfer->declined_at    = now();
            $transfer->decline_reason = trim($data['decline_reason']);
            $transfer->save();
        });

        $transfer->load(['items.product', 'from_user', 'to_user']);

        $this->notifySender($transfer);

        return $transfer;
    }

    /**
     * The sender withdraws it, which is open right up until the receiving
     * employee has accepted or refused it.
     *
     * @throws ValidationException
     */
    public function cancel(StockTransfer $transfer, $userId)
    {
        if (!$transfer->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This transfer is already ' . strtolower($transfer->status_label)
                    . ' and can no longer be cancelled.',
            ]);
        }
        if ((int) $transfer->from_user_id !== (int) $userId) {
            throw ValidationException::withMessages([
                'status' => 'Only the employee who raised a transfer can cancel it.',
            ]);
        }

        DB::transaction(function () use ($transfer) {
            $this->returnEverythingToSender($transfer);

            $transfer->status       = StockTransfer::STATUS_CANCELLED;
            $transfer->cancelled_at = now();
            $transfer->save();
        });

        return $transfer->load(['items.product', 'from_user', 'to_user']);
    }

    /**
     * What an employee can actually offer: the products they hold, with the
     * qty free to transfer and the qty already blocked behind other requests.
     */
    public function transferableStock($userId)
    {
        $rows = FreeSamplingStock::with('productinfo')
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('stock_in_hand', '>', 0)->orWhere('blocked_stock', '>', 0);
            })
            ->get();

        /*
         | The batches listed here are the employee's own: how much of each
         | batch they are holding, not how much of it is left in stores. The
         | batch quantities therefore add up to available_qty, which is the
         | number the app shows them.
         */
        $holdings = app(EmployeeBatchStockService::class)
            ->holdings($userId, $rows->pluck('product_id')->all());

        return $rows->map(function ($row) use ($holdings) {
            $held = isset($holdings[$row->product_id])
                ? $holdings[$row->product_id]
                : ['batches' => [], 'batched_qty' => 0, 'unbatched_qty' => (float) $row->stock_in_hand];

            return [
                'product_id'     => $row->product_id,
                'product_name'   => optional($row->productinfo)->product_name,
                'product_code'   => optional($row->productinfo)->product_code,
                'available_qty'  => (float) $row->stock_in_hand,
                'blocked_qty'    => (float) $row->blocked_stock,
                'in_transit_qty' => (float) $row->in_transit,
                'batch_count'    => count($held['batches']),
                // Stock they hold that no batch can be put against — it moved
                // before batches were recorded, or through a path that does
                // not name one. Normally zero.
                'unbatched_qty'  => $held['unbatched_qty'],
                'batches'        => $held['batches'],
            ];
        })->values();
    }

    /* ═══════════════════════════════════════════════════════════════
       Stock movements — every one of them locks the row first
       ═══════════════════════════════════════════════════════════════ */

    /**
     * stock_in_hand → blocked_stock, refusing the whole transfer when the
     * employee does not hold enough.
     */
    private function blockStock($userId, $productId, $qty)
    {
        $stock = $this->lockRow($userId, $productId);

        if (!$stock || $stock->stock_in_hand + 0.0001 < $qty) {
            $product   = \App\Product::find($productId, ['product_name']);
            $available = $stock ? $this->trim($stock->stock_in_hand) : '0';

            throw ValidationException::withMessages([
                'items' => 'You do not have enough stock of '
                    . ($product ? $product->product_name : 'this product')
                    . ' — ' . $available . ' Kg available.',
            ]);
        }

        $stock->stock_in_hand = round($stock->stock_in_hand - $qty, 3);
        $stock->blocked_stock = round($stock->blocked_stock + $qty, 3);
        $stock->save();
    }

    /**
     * Take the qty out of blocked_stock. $giveBack says whether it returns to
     * the same employee's own stock (declined, cancelled) or leaves them for
     * good because someone else has taken it (approved).
     */
    private function releaseBlocked($userId, $productId, $qty, $giveBack = true)
    {
        $stock = $this->lockRow($userId, $productId);

        if (!$stock) {
            return;
        }

        $stock->blocked_stock = max(0, round($stock->blocked_stock - $qty, 3));

        if ($giveBack) {
            $stock->stock_in_hand = round($stock->stock_in_hand + $qty, 3);
        }

        $stock->save();
    }

    /** The receiving employee's stock in hand, creating their row if needed. */
    private function creditStock($userId, $productId, $qty)
    {
        $stock = $this->lockRow($userId, $productId);

        if (!$stock) {
            $stock = new FreeSamplingStock();
            $stock->user_id                 = $userId;
            $stock->product_id              = $productId;
            $stock->stock_in_hand           = 0;
            $stock->blocked_stock           = 0;
            $stock->in_transit              = 0;
            $stock->pending_orders          = 0;
            $stock->pending_customer_orders = 0;
        }

        $stock->stock_in_hand = round($stock->stock_in_hand + $qty, 3);
        $stock->save();
    }

    private function returnEverythingToSender(StockTransfer $transfer)
    {
        foreach ($transfer->items as $item) {
            $this->releaseBlocked($transfer->from_user_id, $item->product_id, $item->qty, true);
        }
    }

    private function lockRow($userId, $productId)
    {
        return FreeSamplingStock::where('user_id', $userId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();
    }

    /* ═══════════════════════════════════════════════════════════════
       Guards & helpers
       ═══════════════════════════════════════════════════════════════ */

    private function assertDecidable(StockTransfer $transfer, $userId)
    {
        if (!$transfer->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This transfer is already ' . strtolower($transfer->status_label) . '.',
            ]);
        }
        if ((int) $transfer->to_user_id !== (int) $userId) {
            throw ValidationException::withMessages([
                'status' => 'Only the employee the stock was offered to can accept or refuse it.',
            ]);
        }
    }

    private function assertMarketingEmployee($userId, $field, $message)
    {
        $isMarketing = User::marketingEmployees()->where('users.id', $userId)->exists();

        if (!$isMarketing) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }

    /**
     * One qty per product *and batch*. The same product may be sent out of two
     * batches on one transfer, so those stay two rows; the same product and
     * batch twice is added up rather than inserted twice, which the unique
     * index would refuse anyway.
     *
     * Every row must name a batch that exists and belongs to that product —
     * the batch travels with the material so it can be traced afterwards.
     *
     * @return array [['product_id' => int, 'qty' => float, 'batch' => ProductBatch], ...]
     */
    private function normaliseItems(array $items)
    {
        $items   = array_values($items);
        $batches = app(ProductBatchService::class)->lock(array_map(function ($item) {
            return isset($item['batch_ref']) ? $item['batch_ref'] : null;
        }, $items));

        $merged = [];

        foreach ($items as $i => $item) {
            $productId = (int) $item['product_id'];
            $qty       = round((float) $item['qty'], 3);

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a transfer qty greater than zero.',
                ]);
            }

            $batch = $this->resolveBatch($item, $productId, $batches, $i);
            $key   = $productId . '|' . $batch->ref();

            if (isset($merged[$key])) {
                $merged[$key]['qty'] = round($merged[$key]['qty'] + $qty, 3);
                continue;
            }

            $merged[$key] = ['product_id' => $productId, 'qty' => $qty, 'batch' => $batch];
        }

        if (!$merged) {
            throw ValidationException::withMessages(['items' => 'Add at least one product to transfer.']);
        }

        return array_values($merged);
    }

    /**
     * The batch a row is being sent out of.
     *
     * Only the batch's identity is checked here, never its remaining stock:
     * this material already left that batch when it was dispatched to the
     * employee, and what limits a transfer is what the employee holds — see
     * blockStock().
     *
     * @return \App\Support\ProductBatch
     * @throws ValidationException
     */
    private function resolveBatch(array $item, $productId, array $batches, $i)
    {
        $key = "items.$i.batch_ref";
        $ref = isset($item['batch_ref']) ? $item['batch_ref'] : null;

        if (!$ref || !isset($batches[$ref])) {
            throw ValidationException::withMessages([
                $key => 'Select the batch this stock is from. Send the batch_ref from the stock list, e.g. "inventory:45".',
            ]);
        }

        $batch = $batches[$ref];

        if ((int) $batch->productId !== (int) $productId) {
            throw ValidationException::withMessages([
                $key => 'Batch ' . $batch->batchNo . ' belongs to a different product.',
            ]);
        }

        return $batch;
    }

    /**
     * A row may not take more out of a batch than the sender holds of it.
     *
     * Only enforced for products whose stock is fully accounted for by batch.
     * Where some of their stock cannot be put against a batch — material that
     * moved before batches were recorded — the product-level check in
     * blockStock() is the only honest one, and this stands aside rather than
     * refusing a transfer the employee can actually make.
     *
     * @throws ValidationException
     */
    private function assertBatchStock($userId, array $rows)
    {
        $holdings = app(EmployeeBatchStockService::class)
            ->holdings($userId, array_unique(array_column($rows, 'product_id')));

        foreach ($rows as $row) {
            $held = isset($holdings[$row['product_id']]) ? $holdings[$row['product_id']] : null;

            // Stands aside only when some of their stock cannot be put
            // against a batch — not when they have simply used it all up.
            if (!$held || $held['unbatched_qty'] > 0) {
                continue;
            }

            $available = 0;
            foreach ($held['batches'] as $batch) {
                if ($batch['batch_ref'] === $row['batch']->ref()) {
                    $available = $batch['available_qty'];
                    break;
                }
            }

            if ($row['qty'] > $available + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => 'You are holding ' . $this->trim($available) . ' Kg of batch '
                        . $row['batch']->batchNo . ' — ' . $this->trim($row['qty']) . ' Kg cannot be sent out of it.',
                ]);
            }
        }
    }

    /** What each product adds up to across the rows of one transfer. */
    private function totalsByProduct(array $rows)
    {
        $totals = [];

        foreach ($rows as $row) {
            $totals[$row['product_id']] = isset($totals[$row['product_id']])
                ? round($totals[$row['product_id']] + $row['qty'], 3)
                : $row['qty'];
        }

        return $totals;
    }

    /** ST-1, ST-2, … a plain running number that never resets. */
    private function nextNumber()
    {
        return (int) StockTransfer::lockForUpdate()->max('transfer_no') + 1;
    }

    private function trim($value)
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }

    /* ═══════════════════════════════════════════════════════════════
       Emails
       ═══════════════════════════════════════════════════════════════ */

    /** The employee being offered the stock is told there is something to do. */
    private function notifyReceiver(StockTransfer $transfer)
    {
        $receiver = $transfer->to_user;

        if (!$receiver || !$receiver->email) {
            return;
        }

        EmailService::send(self::EVENT_REQUESTED, [
            'transfer' => $transfer,
            'sender'   => $transfer->from_user,
            'receiver' => $receiver,
        ], $receiver->email);
    }

    /**
     * The employee who raised it is told what happened — accepted or refused.
     * One template serves both; the view reads the status.
     */
    private function notifySender(StockTransfer $transfer)
    {
        $sender = $transfer->from_user;

        if (!$sender || !$sender->email) {
            return;
        }

        EmailService::send(self::EVENT_DECIDED, [
            'transfer' => $transfer,
            'sender'   => $sender,
            'receiver' => $transfer->to_user,
        ], $sender->email);
    }
}
