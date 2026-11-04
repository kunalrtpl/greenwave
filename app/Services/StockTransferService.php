<?php

namespace App\Services;

use App\FreeSamplingStock;
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

            foreach ($this->normaliseItems($data['items']) as $productId => $qty) {
                // Blocking first: if the sender does not hold it, nothing is
                // created at all, because the whole thing is one transaction.
                $this->blockStock($fromUserId, $productId, $qty);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $productId,
                    'qty'               => $qty,
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
        return FreeSamplingStock::with('productinfo')
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('stock_in_hand', '>', 0)->orWhere('blocked_stock', '>', 0);
            })
            ->get()
            ->map(function ($row) {
                return [
                    'product_id'     => $row->product_id,
                    'product_name'   => optional($row->productinfo)->product_name,
                    'product_code'   => optional($row->productinfo)->product_code,
                    'available_qty'  => (float) $row->stock_in_hand,
                    'blocked_qty'    => (float) $row->blocked_stock,
                    'in_transit_qty' => (float) $row->in_transit,
                ];
            })
            ->values();
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
     * One qty per product: the same product twice in a payload is added up
     * rather than inserted twice, which the unique index would refuse anyway.
     *
     * @return array product_id => qty
     */
    private function normaliseItems(array $items)
    {
        $merged = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $qty       = round((float) $item['qty'], 3);

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a transfer qty greater than zero.',
                ]);
            }

            $merged[$productId] = isset($merged[$productId]) ? round($merged[$productId] + $qty, 3) : $qty;
        }

        if (!$merged) {
            throw ValidationException::withMessages(['items' => 'Add at least one product to transfer.']);
        }

        return $merged;
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
