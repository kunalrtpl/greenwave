<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\Product;
use App\SamplingDispatch;
use App\SamplingRequest;
use App\SamplingRequestLog;
use App\Services\SamplingRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Step 2 of the flow: send approved material out.
 *
 * One save can cover several request lines. The challan and transport details
 * are common to the whole save; "dispatch to" is captured per request line,
 * because two requests on one challan can be going to different parties. A
 * voluntary dispatch picks one destination for all its products, but still
 * stores a copy against each one.
 *
 * A line is closed only when the dispatched total reaches the approved qty —
 * approve 5 of 10 and send 3, and the line stays approved with 2 pending.
 */
class SamplingDispatchService
{
    /**
     * @param  array $data validated payload from StoreSamplingDispatchRequest
     * @return array ['dispatch_ref_no' => string, 'lines' => int, 'closed' => int]
     * @throws ValidationException
     */
    public function dispatch(array $data)
    {
        $notify = [];

        $result = DB::transaction(function () use ($data, &$notify) {
            // A voluntary dispatch has no request behind it, so create one
            // first — already approved and ready — and then send it out
            // through exactly the same path as everything else.
            $items = isset($data['dispatch_type']) && $data['dispatch_type'] === 'voluntary'
                ? $this->createVoluntaryRequests($data)
                : $data['items'];

            $requests = $this->lockRequests($items);
            $lines    = $this->validateLines($items, $requests);

            $dispatch     = $this->nextDispatchNo();
            $dispatchedBy = \Auth::check() ? \Auth::user()->id : null;
            $closedCount  = 0;

            foreach ($lines as $line) {
                /** @var SamplingRequest $request */
                $request = $line['request'];
                $qty     = $line['qty'];

                SamplingDispatch::create([
                    'dispatch_no'          => $dispatch['dispatch_no'],
                    'dispatch_ref_no'      => $dispatch['dispatch_ref_no'],
                    'financial_year'       => $dispatch['financial_year'],
                    'dispatch_date'        => $data['dispatch_date'],
                    'sampling_request_id'  => $request->id,
                    'product_id'           => $request->product_id,
                    'batch_no'             => $line['batch_no'],
                    'dispatch_qty'         => $qty,
                    'invoice_no'           => $data['invoice_no'],
                    'invoice_date'         => isset($data['invoice_date']) ? $data['invoice_date'] : $data['dispatch_date'],
                    'transport'            => $data['transport'],
                    'lr_no'                => $data['lr_no'],
                    'remarks'              => isset($data['remarks']) ? $data['remarks'] : null,
                    'dispatch_to'          => $line['dispatch_to'],
                    'dispatch_dealer_id'   => $line['dispatch_dealer_id'],
                    'dispatch_customer_id' => $line['dispatch_customer_id'],
                    'dispatch_user_id'     => $line['dispatch_user_id'],
                    'dispatch_address'     => $line['dispatch_address'],
                    'dispatched_by_user_id' => $dispatchedBy,
                ]);

                $request->total_dispatched_qty = round($request->total_dispatched_qty + $qty, 3);

                // Remember where this one actually went, so the next partial
                // dispatch of the same line prefills with the same party.
                $request->dispatch_to          = $line['dispatch_to'];
                $request->dispatch_dealer_id   = $line['dispatch_dealer_id'];
                $request->dispatch_customer_id = $line['dispatch_customer_id'];
                $request->dispatch_user_id     = $line['dispatch_user_id'];
                $request->dispatch_address     = $line['dispatch_address'];

                $isClosed = $request->total_dispatched_qty >= $request->approved_qty - 0.0001;
                if ($isClosed) {
                    $request->status    = SamplingRequest::STATUS_CLOSED;
                    $request->closed_at = now();
                    $closedCount++;
                }
                $request->save();

                $this->moveStockToTransit($request, $qty);

                $notify[] = [
                    'request'  => $request,
                    'qty'      => $qty,
                    'batch_no' => $line['batch_no'],
                ];

                SamplingRequestLog::record(
                    $request->id,
                    SamplingRequestLog::ACTION_DISPATCHED,
                    [
                        'dispatch_qty' => ['label' => 'Dispatched Qty', 'from' => null, 'to' => $qty],
                        'batch_no'     => ['label' => 'Batch No', 'from' => null, 'to' => $line['batch_no']],
                    ],
                    'Dispatched on ' . $dispatch['dispatch_ref_no'] . ' (challan ' . $data['invoice_no'] . ')'
                );

                if ($isClosed) {
                    SamplingRequestLog::record(
                        $request->id,
                        SamplingRequestLog::ACTION_CLOSED,
                        [],
                        'Full approved qty dispatched'
                    );
                }
            }

            return [
                'dispatch_ref_no' => $dispatch['dispatch_ref_no'],
                'lines'           => count($lines),
                'closed'          => $closedCount,
                'dispatch_no'     => $dispatch['dispatch_no'],
            ];
        });

        // Outside the transaction: a bounced email must never undo a dispatch.
        app(SamplingEmailService::class)->dispatched($notify, [
            'ref_no'     => $result['dispatch_ref_no'],
            'date'       => date('d M Y', strtotime($data['dispatch_date'])),
            'invoice_no' => isset($data['invoice_no']) ? $data['invoice_no'] : null,
            'transport'  => isset($data['transport']) ? $data['transport'] : '',
            'lr_no'      => isset($data['lr_no']) ? $data['lr_no'] : null,
            'address'    => count($notify) ? $notify[0]['request']->dispatch_address : null,
        ]);

        return $result;
    }

    /* ─────────────────────────── Receipt ─────────────────────────── */

    /**
     * The employee confirms from the app that dispatched material arrived.
     *
     * This is the other half of moveStockToTransit(): what dispatching put in
     * transit becomes stock in hand, exactly as the old
     * updateFreeSampleMaterialDelivery() did with sampling_sale_invoices.
     *
     * Only the employee's own lines can be confirmed, and only once — a line
     * already marked received is counted as already_received and left alone,
     * so a retried post can never move the same qty twice.
     *
     * @param  array  $ids    sampling_dispatches ids
     * @param  int    $userId the confirming employee
     * @param  string $when   optional Y-m-d H:i:s, defaults to now
     * @return array ['received' => int, 'already_received' => int, 'dispatches' => Collection]
     * @throws ValidationException
     */
    public function markReceived(array $ids, $userId, $when = null)
    {
        $ids = array_values(array_unique(array_filter($ids)));

        return DB::transaction(function () use ($ids, $userId, $when) {
            $rows = SamplingDispatch::with('sampling_request')
                ->whereIn('id', $ids)
                ->forEmployee($userId)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Anything that is not theirs is named rather than silently
            // ignored, so the app cannot think it confirmed something it did not.
            $missing = array_diff($ids, $rows->keys()->all());
            if ($missing) {
                throw ValidationException::withMessages([
                    'dispatch_ids' => 'These dispatches are not yours or no longer exist: ' . implode(', ', $missing),
                ]);
            }

            $received = 0;
            $already  = 0;

            foreach ($rows as $row) {
                if ($row->is_received) {
                    $already++;
                    continue;
                }

                $row->is_received = 1;
                $row->received_at = $when ?: now();
                $row->save();

                $this->moveStockToHand($row);

                SamplingRequestLog::record(
                    $row->sampling_request_id,
                    SamplingRequestLog::ACTION_RECEIVED,
                    [
                        'dispatch_qty' => ['label' => 'Received Qty', 'from' => null, 'to' => (float) $row->dispatch_qty],
                    ],
                    'Received against ' . $row->dispatch_ref_no,
                    $userId
                );

                $received++;
            }

            return [
                'received'         => $received,
                'already_received' => $already,
                'dispatches'       => $rows->values(),
            ];
        });
    }

    /**
     * In transit becomes stock in hand for whoever the dispatch credited —
     * the executive the request belongs to, falling back to whoever the
     * material was addressed to for a voluntary dealer sample.
     */
    private function moveStockToHand(SamplingDispatch $dispatch)
    {
        $request = $dispatch->sampling_request;
        $userId  = optional($request)->user_id ?: $dispatch->dispatch_user_id;

        if (!$userId || !$dispatch->product_id) {
            return;
        }

        $stock = FreeSamplingStock::where('user_id', $userId)
            ->where('product_id', $dispatch->product_id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            return;
        }

        $qty = (float) $dispatch->dispatch_qty;

        $stock->in_transit    = max(0, $stock->in_transit - $qty);
        $stock->stock_in_hand = $stock->stock_in_hand + $qty;
        $stock->save();
    }

    /* ─────────────────────────── Voluntary ─────────────────────────── */

    /**
     * Turn each voluntary row into a sampling_requests row, then hand back
     * item rows in the ordinary shape so the rest of the flow is unchanged.
     */
    private function createVoluntaryRequests(array $data)
    {
        $service = app(SamplingRequestService::class);
        $items   = [];

        // A dealer's voluntary sample is answerable to the dealer; everyone
        // else's is answerable to the executive. The key is optional so an
        // internal caller can post an executive dispatch without it.
        $owner = (isset($data['answerable_type']) && $data['answerable_type'] === 'dealer')
            ? ['dealer_id' => $data['dealer_id']]
            : ['user_id' => isset($data['user_id']) ? $data['user_id'] : null];

        foreach (array_values($data['voluntary_items']) as $row) {
            // The form posts an empty string for the two party ids the chosen
            // type does not use, and "" is not NULL as far as a foreign key
            // is concerned — so blank them out properly.
            $party = [
                'dispatch_to'          => isset($row['dispatch_party_type']) ? ucfirst($row['dispatch_party_type'] === 'user' ? 'employee' : $row['dispatch_party_type']) : null,
                'dispatch_dealer_id'   => $this->blankToNull($row, 'dispatch_dealer_id'),
                'dispatch_customer_id' => $this->blankToNull($row, 'dispatch_customer_id'),
                'dispatch_user_id'     => $this->blankToNull($row, 'dispatch_user_id'),
                'dispatch_address'     => $this->blankToNull($row, 'dispatch_address'),
            ];

            $request = $service->createVoluntary($row, $owner, $party);

            $items[] = [
                'sampling_request_id' => $request->id,
                'dispatch_qty'        => $request->approved_qty,
                'batch_no'            => isset($row['batch_no']) ? $row['batch_no'] : null,
                'dispatch_party_type' => isset($row['dispatch_party_type']) ? $row['dispatch_party_type'] : null,
                'dispatch_dealer_id'  => $party['dispatch_dealer_id'],
                'dispatch_customer_id' => $party['dispatch_customer_id'],
                'dispatch_user_id'    => $party['dispatch_user_id'] ?: (isset($owner['user_id']) ? $owner['user_id'] : null),
                'dispatch_address'    => $party['dispatch_address'],
            ];
        }

        return $items;
    }

    private function blankToNull(array $row, $key)
    {
        if (!isset($row[$key])) {
            return null;
        }
        $value = is_string($row[$key]) ? trim($row[$key]) : $row[$key];

        return ($value === '' || $value === null) ? null : $value;
    }

    /* ─────────────────────────── Validation ─────────────────────────── */

    /** Row-locks every request in the save so two admins cannot over-dispatch the same line. */
    private function lockRequests(array $items)
    {
        $ids = array_filter(array_column($items, 'sampling_request_id'));

        return SamplingRequest::whereIn('id', $ids)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function validateLines(array $items, $requests)
    {
        $lines  = [];
        $errors = [];

        foreach (array_values($items) as $i => $row) {
            $request = $requests->get($row['sampling_request_id']);
            $qty     = (float) $row['dispatch_qty'];

            if (!$request) {
                $errors["items.$i.sampling_request_id"] = 'This sample request no longer exists.';
                continue;
            }
            if (!$request->isApproved()) {
                $errors["items.$i.dispatch_qty"] = $request->reference_no . ' is ' . strtolower($request->status_label) . ' and cannot be dispatched.';
                continue;
            }
            if (!$request->is_ready) {
                $errors["items.$i.dispatch_qty"] = $request->reference_no . ' has not been marked ready yet.';
                continue;
            }
            if ($qty <= 0) {
                $errors["items.$i.dispatch_qty"] = 'Enter a dispatch qty greater than zero.';
                continue;
            }
            if ($qty > $request->pending_qty + 0.0001) {
                $errors["items.$i.dispatch_qty"] = 'Cannot exceed the pending qty of ' . $request->pending_qty . ' kg.';
                continue;
            }

            $party = $this->resolveDispatchParty($row, $request);

            $lines[] = [
                'request'              => $request,
                'qty'                  => $qty,
                'batch_no'             => isset($row['batch_no']) ? $row['batch_no'] : null,
                'dispatch_to'          => $party['dispatch_to'],
                'dispatch_dealer_id'   => $party['dispatch_dealer_id'],
                'dispatch_customer_id' => $party['dispatch_customer_id'],
                'dispatch_user_id'     => $party['dispatch_user_id'],
                'dispatch_address'     => $this->blankToNull($row, 'dispatch_address') ?: $request->dispatch_address,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        if (!$lines) {
            throw ValidationException::withMessages(['items' => 'Select at least one sample request to dispatch.']);
        }

        return $lines;
    }

    /**
     * Exactly one of dealer / customer / user is kept. When the form sends
     * nothing usable, the request's own dispatch-to is reused, and the
     * requesting executive is the last fallback.
     */
    private function resolveDispatchParty(array $row, SamplingRequest $request)
    {
        $type = isset($row['dispatch_party_type']) ? $row['dispatch_party_type'] : null;

        if ($type === 'dealer' && !empty($row['dispatch_dealer_id'])) {
            return [
                'dispatch_to'          => 'Dealer',
                'dispatch_dealer_id'   => $row['dispatch_dealer_id'],
                'dispatch_customer_id' => null,
                'dispatch_user_id'     => null,
            ];
        }
        if ($type === 'customer' && !empty($row['dispatch_customer_id'])) {
            return [
                'dispatch_to'          => 'Customer',
                'dispatch_dealer_id'   => null,
                'dispatch_customer_id' => $row['dispatch_customer_id'],
                'dispatch_user_id'     => null,
            ];
        }
        if ($type === 'user') {
            return [
                'dispatch_to'          => 'Employee',
                'dispatch_dealer_id'   => null,
                'dispatch_customer_id' => null,
                'dispatch_user_id'     => !empty($row['dispatch_user_id']) ? $row['dispatch_user_id'] : $request->user_id,
            ];
        }

        return [
            'dispatch_to'          => $request->dispatch_to,
            'dispatch_dealer_id'   => $request->dispatch_dealer_id,
            'dispatch_customer_id' => $request->dispatch_customer_id,
            'dispatch_user_id'     => $request->dispatch_dealer_id || $request->dispatch_customer_id
                ? $request->dispatch_user_id
                : ($request->dispatch_user_id ?: $request->user_id),
        ];
    }

    /* ─────────────────────────── Numbering & stock ─────────────────────────── */

    private function nextDispatchNo()
    {
        $financialYear = financialYear();

        $last = SamplingDispatch::where('financial_year', $financialYear)
            ->lockForUpdate()
            ->max('dispatch_no');
        $number = (int) $last + 1;

        return [
            'dispatch_no'     => $number,
            'dispatch_ref_no' => 'SD-' . $number . '/' . $financialYear,
            'financial_year'  => $financialYear,
        ];
    }

    /**
     * The reservation made at approval becomes in-transit stock, which the
     * executive's app converts to stock-in-hand when the material arrives.
     * Factory stock drops by the same qty.
     */
    private function moveStockToTransit(SamplingRequest $request, $qty)
    {
        if ($request->product_id) {
            Product::where('id', $request->product_id)->decrement('current_stock', $qty);
        }

        if (!$request->user_id || !$request->product_id) {
            return;
        }

        $stock = FreeSamplingStock::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            $stock = new FreeSamplingStock();
            $stock->user_id                 = $request->user_id;
            $stock->customer_id             = $request->customer_id;
            $stock->product_id              = $request->product_id;
            $stock->stock_in_hand           = 0;
            $stock->in_transit              = 0;
            $stock->pending_orders          = 0;
            $stock->pending_customer_orders = 0;
        }

        $stock->pending_orders = max(0, $stock->pending_orders - $qty);
        $stock->in_transit     = $stock->in_transit + $qty;
        $stock->save();
    }
}
