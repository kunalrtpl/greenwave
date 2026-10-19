<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\Product;
use App\SamplingRequest;
use App\SamplingRequestLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Everything that happens to a sample request before it is dispatched:
 * creation from the API, admin edits, approval, decline and the ready mark.
 *
 * Dispatching lives in SamplingDispatchService.
 */
class SamplingRequestService
{
    /**
     * Fields the admin may change on a pending line. The keys are also what
     * the audit log records, so renaming one changes the log label too.
     */
    const EDITABLE_FIELDS = [
        'product_id'                 => 'Product',
        'approved_pack_size'         => 'Pack Size',
        'approved_no_of_packs'       => 'No of Packs',
        'customer_id'                => 'Customer',
        'purpose_reason_for_request' => 'Purpose / Reason',
        'application_plan'           => 'Application Plan',
        'dispatch_to'                => 'Dispatch To',
        'dispatch_dealer_id'         => 'Dispatch Dealer',
        'dispatch_customer_id'       => 'Dispatch Customer',
        'dispatch_user_id'           => 'Dispatch User',
        'dispatch_address'           => 'Dispatch Address',
        'competitor_category'        => 'Competitor Category',
        'competitor_product_name'    => 'Competitor Product',
        'competitor_make'            => 'Competitor Make',
        'competitor_dealer_price'    => 'Competitor Dealer Price',
        'competitor_customer_price'  => 'Competitor Customer Price',
        'competitor_dosage'          => 'Competitor Dosage',
        'competitor_monthly_requirement_kg' => 'Competitor Monthly Requirement',
        'competitor_specialty'       => 'Competitor Specialty',
        'competitor_application_details' => 'Competitor Application Details',
        'competitor_expectation'     => 'Competitor Expectation',
        'is_specific_customer'       => 'Specific Customer',
        'product_known'              => 'Product Known',
        'target_competitor'          => 'Target Competitor',
    ];

    /** Context columns copied onto every line of the same request. */
    const REQUEST_LEVEL_FIELDS = [
        'purpose_reason_for_request', 'application_plan', 'gst',
        'is_specific_customer', 'product_known', 'target_competitor',
        'competitor_category', 'competitor_product_name', 'competitor_make',
        'competitor_dealer_price', 'competitor_customer_price', 'competitor_dosage',
        'competitor_monthly_requirement_kg', 'competitor_specialty',
        'competitor_application_details', 'competitor_expectation',
        'dispatch_to', 'dispatch_dealer_id', 'dispatch_customer_id',
        'dispatch_user_id', 'dispatch_address', 'customer_id',
    ];

    /* ═══════════════════════════════════════════════════════════════
       CREATE
       ═══════════════════════════════════════════════════════════════ */

    /**
     * Turn one API post into one row per product.
     *
     * @param  array $data     validated payload
     * @param  int   $userId   authenticated executive raising the request
     * @return \Illuminate\Support\Collection of SamplingRequest
     */
    public function createFromApi(array $data, $userId)
    {
        $created = DB::transaction(function () use ($data, $userId) {
            $financialYear = financialYear();
            $groupNo       = $this->nextGroupNo();

            // When no dealer and no customer is named, the material goes to the
            // executive who raised the request.
            $dispatchDealerId   = !empty($data['dispatch_dealer_id']) ? $data['dispatch_dealer_id'] : null;
            $dispatchCustomerId = !empty($data['dispatch_customer_id']) ? $data['dispatch_customer_id'] : null;
            $dispatchUserId     = ($dispatchDealerId || $dispatchCustomerId) ? null : $userId;

            $context = [
                'financial_year'   => $financialYear,
                'request_group_no' => $groupNo,
                'raised_by'        => 'user',
                'user_id'          => $userId,
                'dealer_id'        => null,
                'customer_id'      => !empty($data['customer_id']) ? $data['customer_id'] : null,
                'sample_type'      => !empty($data['sample_type']) ? $data['sample_type'] : 'free',
                'status'           => SamplingRequest::STATUS_PENDING,
                'request_date'     => date('Y-m-d'),

                'purpose_reason_for_request' => $this->nullableText($data, 'purpose_reason_for_request'),
                'application_plan'           => $this->nullableText($data, 'application_plan'),
                'gst'                        => isset($data['gst']) ? $data['gst'] : null,
                'is_specific_customer'       => !empty($data['is_specific_customer']) ? 1 : 0,
                'product_known'              => !empty($data['product_known']) ? 1 : 0,
                'target_competitor'          => !empty($data['target_competitor']) ? 1 : 0,

                'competitor_category'               => $this->nullableText($data, 'competitor_category'),
                'competitor_product_name'           => $this->nullableText($data, 'competitor_product_name'),
                'competitor_make'                   => $this->nullableText($data, 'competitor_make'),
                'competitor_dealer_price'           => $this->nullableNumber($data, 'competitor_dealer_price'),
                'competitor_customer_price'         => $this->nullableNumber($data, 'competitor_customer_price'),
                'competitor_dosage'                 => $this->nullableText($data, 'competitor_dosage'),
                'competitor_monthly_requirement_kg' => $this->nullableNumber($data, 'competitor_monthly_requirement_kg'),
                'competitor_specialty'              => $this->nullableText($data, 'competitor_specialty'),
                'competitor_application_details'    => $this->nullableText($data, 'competitor_application_details'),
                'competitor_expectation'            => $this->nullableText($data, 'competitor_expectation'),

                'dispatch_to'          => $this->nullableText($data, 'dispatch_to'),
                'dispatch_dealer_id'   => $dispatchDealerId,
                'dispatch_customer_id' => $dispatchCustomerId,
                'dispatch_user_id'     => $dispatchUserId,
                'dispatch_address'     => $this->nullableText($data, 'dispatch_address'),
            ];

            $products = Product::whereIn('id', array_column($data['items'], 'product_id'))
                ->get(['id', 'product_detail_id', 'physical_form'])
                ->keyBy('id');

            $created = collect();

            foreach (array_values($data['items']) as $index => $item) {
                $lineNo  = $index + 1;
                $product = $products->get($item['product_id']);

                $request = SamplingRequest::create($context + [
                    'line_no'              => $lineNo,
                    'reference_no'         => 'SR-' . $groupNo . '.' . $lineNo,
                    'requested_product_id' => $item['product_id'],
                    'product_id'           => $item['product_id'],
                    'product_detail_id'    => $product ? $product->product_detail_id : null,
                    'pack_size_info'       => isset($item['pack_size_info']) ? $item['pack_size_info'] : null,
                    'pack_size'            => isset($item['pack_size']) ? $item['pack_size'] : null,
                    'no_of_packs'          => isset($item['no_of_packs']) ? $item['no_of_packs'] : null,
                    'requested_qty'        => $item['qty'],
                    'item_remarks'         => isset($item['remarks']) ? $item['remarks'] : null,

                    // The approve form opens pre-filled with what was asked for;
                    // the admin only touches it when something has to change.
                    'approved_pack_size'   => $this->defaultPackSize($product, isset($item['pack_size']) ? $item['pack_size'] : null),
                    'approved_no_of_packs' => isset($item['no_of_packs']) ? $item['no_of_packs'] : null,
                    'total_dispatched_qty' => 0,
                ]);

                SamplingRequestLog::record(
                    $request->id,
                    SamplingRequestLog::ACTION_CREATED,
                    [],
                    'Received from the mobile app',
                    $userId
                );

                $created->push($request);
            }

            return $created;
        });

        // Outside the transaction: a bounced email must never undo the rows.
        app(SamplingEmailService::class)->requestRaised($created);

        return $created;
    }

    /**
     * One running counter for the whole system: SR-1.x, SR-2.x, SR-3.x …
     * It deliberately does not reset each financial year, so a reference on
     * its own always identifies exactly one request.
     */
    private function nextGroupNo()
    {
        $last = SamplingRequest::lockForUpdate()->max('request_group_no');

        return (int) $last + 1;
    }

    /**
     * A request the admin raises at dispatch time, with nothing having come
     * from the field. It is born already approved and ready, because the
     * decision and the dispatch are the same action — `is_voluntarily` is what
     * tells the reports these two apart.
     *
     * @param  array $item   product_id, approved_pack_size, approved_no_of_packs, remarks
     * @param  array $owner  ['user_id' => int] or ['dealer_id' => int] — who the
     *                       sample is answerable to. A dealer's own samples are
     *                       always voluntary, since a dealer raises nothing
     *                       through the executive app.
     * @return SamplingRequest
     */
    public function createVoluntary(array $item, array $owner, array $context = [])
    {
        $product = Product::find($item['product_id'], ['id', 'product_detail_id', 'physical_form']);

        $packSize = (float) $item['approved_pack_size'];
        $packs    = (int) $item['approved_no_of_packs'];

        $allowed = SamplingRequest::packSizesFor($product ? $product->physical_form : null);
        if (!in_array($packSize, array_map('floatval', $allowed))) {
            throw ValidationException::withMessages([
                'voluntary_items' => 'This product can only be packed in ' . implode(' or ', $allowed) . ' Kg packs.',
            ]);
        }

        $qty         = round($packSize * $packs, 3);
        $dealerPrice = $this->dealerPrice($item['product_id']);
        $groupNo     = $this->nextGroupNo();
        $adminId     = \Auth::check() ? \Auth::user()->id : null;

        $request = SamplingRequest::create($context + [
            'financial_year'       => financialYear(),
            'request_group_no'     => $groupNo,
            'line_no'              => 1,
            'reference_no'         => 'SR-' . $groupNo . '.1',
            'raised_by'            => !empty($owner['dealer_id']) ? 'dealer' : 'user',
            'is_voluntarily'       => 1,
            'user_id'              => !empty($owner['user_id']) ? $owner['user_id'] : null,
            'dealer_id'            => !empty($owner['dealer_id']) ? $owner['dealer_id'] : null,
            'sample_type'          => 'free',
            'status'               => SamplingRequest::STATUS_APPROVED,
            'request_date'         => date('Y-m-d'),

            'requested_product_id' => $item['product_id'],
            'pack_size'            => $packSize,
            'no_of_packs'          => $packs,
            'requested_qty'        => $qty,
            'item_remarks'         => isset($item['remarks']) ? $item['remarks'] : null,

            'product_id'           => $item['product_id'],
            'product_detail_id'    => $product ? $product->product_detail_id : null,
            'approved_pack_size'   => $packSize,
            'approved_no_of_packs' => $packs,
            'approved_qty'         => $qty,
            'dealer_price'         => $dealerPrice,
            'approved_value'       => $dealerPrice === null ? null : round($qty * $dealerPrice, 2),
            'total_dispatched_qty' => 0,

            'approved_at'          => now(),
            'approved_by_user_id'  => $adminId,
            'approval_remarks'     => 'Raised and approved by the admin at dispatch time',
            'is_ready'             => 1,
            'ready_at'             => now(),
            'ready_by_user_id'     => $adminId,
        ]);

        // Only an executive carries a free-sample stock bucket; a dealer's
        // sample just leaves the factory.
        $this->reserveExecutiveStock($request, $qty);

        SamplingRequestLog::record(
            $request->id,
            SamplingRequestLog::ACTION_CREATED,
            [],
            'Raised by the admin at dispatch time (voluntary sample)'
        );
        SamplingRequestLog::record(
            $request->id,
            SamplingRequestLog::ACTION_APPROVED,
            ['approved_qty' => ['label' => 'Approved Qty', 'from' => null, 'to' => $qty . ' Kg (' . $packs . ' x ' . $packSize . ' Kg)']],
            'Approved on creation'
        );

        return $request;
    }

    /* ═══════════════════════════════════════════════════════════════
       ADMIN EDIT
       ═══════════════════════════════════════════════════════════════ */

    /**
     * Apply the admin's corrections to a pending line.
     *
     * requested_product_id is deliberately left alone so the detail screen can
     * always show "asked for X, approved as Y". Request-level fields are
     * mirrored onto the other lines of the same request so they stay in sync.
     */
    public function update(SamplingRequest $request, array $data)
    {
        if (!$request->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only a pending request can be edited. This one is already ' . strtolower($request->status_label) . '.',
            ]);
        }

        return DB::transaction(function () use ($request, $data) {
            $changes = [];

            foreach (self::EDITABLE_FIELDS as $field => $label) {
                if (!array_key_exists($field, $data)) {
                    continue;
                }
                $new = $data[$field];
                $old = $request->getAttribute($field);

                if ($this->sameValue($old, $new)) {
                    continue;
                }

                $changes[$field] = [
                    'label' => $label,
                    'from'  => $this->displayValue($field, $old),
                    'to'    => $this->displayValue($field, $new),
                ];
                $request->setAttribute($field, $new);
            }

            if (array_key_exists('product_id', $data) && $data['product_id'] != $request->getOriginal('product_id')) {
                $product = Product::find($data['product_id'], ['id', 'product_detail_id']);
                $request->product_detail_id = $product ? $product->product_detail_id : null;
            }

            if (!$changes) {
                return $request;
            }

            $request->save();
            $this->syncRequestLevelFields($request, array_keys($changes));

            SamplingRequestLog::record(
                $request->id,
                SamplingRequestLog::ACTION_UPDATED,
                $changes,
                isset($data['change_remarks']) ? $data['change_remarks'] : null
            );

            return $request;
        });
    }

    /**
     * Keep the request-level block identical across every line of a request —
     * the table is flat by design, so an edit on one line has to be copied.
     */
    private function syncRequestLevelFields(SamplingRequest $request, array $changedFields)
    {
        $shared = array_intersect($changedFields, self::REQUEST_LEVEL_FIELDS);
        if (!$shared) {
            return;
        }

        $values = [];
        foreach ($shared as $field) {
            $values[$field] = $request->getAttribute($field);
        }

        SamplingRequest::where('request_group_no', $request->request_group_no)
            ->where('id', '!=', $request->id)
            ->whereIn('status', [SamplingRequest::STATUS_PENDING, SamplingRequest::STATUS_APPROVED])
            ->update($values);
    }

    /* ═══════════════════════════════════════════════════════════════
       APPROVE / DECLINE / READY
       ═══════════════════════════════════════════════════════════════ */

    /**
     * Approve a pending line for `approved_qty`, which may be less than what
     * was asked for. The qty is reserved against the executive's free-sample
     * stock so the mobile app shows it as on order.
     */
    public function approve(SamplingRequest $request, array $data)
    {
        if (!$request->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This request is already ' . strtolower($request->status_label) . '.',
            ]);
        }

        DB::transaction(function () use ($request, $data) {
            // Any detail corrections the admin made on the approve form are
            // recorded as an edit first, so the log reads in the right order.
            $this->update($request, $data);

            // Qty is never typed in: it is pack size x no of packs, the same
            // way the old sampling screen worked.
            $packSize = (float) $data['approved_pack_size'];
            $packs    = (int) $data['approved_no_of_packs'];
            $this->assertPackSizeAllowed($request, $packSize);

            $approvedQty = round($packSize * $packs, 3);
            if ($approvedQty <= 0) {
                throw ValidationException::withMessages([
                    'approved_no_of_packs' => 'Approved qty works out to zero. Decline the request instead.',
                ]);
            }

            $dealerPrice = $this->dealerPrice($request->product_id);

            $request->status               = SamplingRequest::STATUS_APPROVED;
            $request->approved_pack_size   = $packSize;
            $request->approved_no_of_packs = $packs;
            $request->approved_qty         = $approvedQty;
            $request->dealer_price         = $dealerPrice;
            $request->approved_value       = $dealerPrice === null ? null : round($approvedQty * $dealerPrice, 2);
            $request->approved_at          = now();
            $request->approved_by_user_id  = \Auth::check() ? \Auth::user()->id : null;
            $request->approval_remarks     = isset($data['approval_remarks']) ? $data['approval_remarks'] : null;
            $request->save();

            $this->reserveExecutiveStock($request, $approvedQty);

            SamplingRequestLog::record(
                $request->id,
                SamplingRequestLog::ACTION_APPROVED,
                ['approved_qty' => [
                    'label' => 'Approved Qty',
                    'from'  => $request->requested_qty . ' Kg requested',
                    'to'    => $approvedQty . ' Kg (' . $packs . ' x ' . $packSize . ' Kg)',
                ]],
                $request->approval_remarks
            );

            return $request;
        });

        // Outside the transaction: a bounced email must never undo an approval.
        app(SamplingEmailService::class)->requestDecided($request);

        return $request;
    }

    /**
     * Refuse a request.
     *
     * Open at every stage until the material actually moves: while it is
     * pending, after it has been approved, and after it has been marked ready
     * — the admin is allowed to change their mind, which is what the client
     * asked for. Declining an approved line gives the reserved qty back to the
     * executive's free-sample stock and takes the line off the dispatch desk.
     *
     * The one thing it cannot undo is a dispatch: once any qty has gone out
     * there is material in the field, so declining is refused and the earlier
     * state is left alone.
     */
    public function decline(SamplingRequest $request, array $data)
    {
        if ($request->status === SamplingRequest::STATUS_DECLINED) {
            throw ValidationException::withMessages([
                'status' => 'This request has already been declined.',
            ]);
        }
        if ($request->total_dispatched_qty > 0) {
            throw ValidationException::withMessages([
                'status' => 'This request cannot be declined — '
                    . rtrim(rtrim(number_format((float) $request->total_dispatched_qty, 3, '.', ''), '0'), '.')
                    . ' Kg has already been dispatched against it.',
            ]);
        }
        if (!$request->isPending() && !$request->isApproved()) {
            throw ValidationException::withMessages([
                'status' => 'A ' . strtolower($request->status_label) . ' request cannot be declined.',
            ]);
        }

        // Captured before the status changes, for the stock release and the log.
        $fromLabel   = $request->status_label;
        $wasReady    = (bool) $request->is_ready;
        $releasedQty = $request->isApproved() ? (float) $request->approved_qty : 0.0;

        DB::transaction(function () use ($request, $data, $fromLabel, $wasReady, $releasedQty) {
            $request->status              = SamplingRequest::STATUS_DECLINED;
            $request->declined_at         = now();
            $request->declined_by_user_id = \Auth::check() ? \Auth::user()->id : null;
            $request->decline_reason      = $data['decline_reason'];

            // Nothing is going out, so the line leaves the dispatch desk. The
            // ready stamp itself is kept: it is part of the history.
            $request->is_ready = 0;
            $request->save();

            if ($releasedQty > 0) {
                $this->releaseExecutiveStock($request, $releasedQty);
            }

            $changes = ['status' => ['label' => 'Status', 'from' => $fromLabel, 'to' => 'Declined']];
            if ($releasedQty > 0) {
                $changes['approved_qty'] = [
                    'label' => 'Approved Qty',
                    'from'  => $releasedQty . ' Kg' . ($wasReady ? ' (was marked ready)' : ''),
                    'to'    => 'Released back to stock',
                ];
            }

            SamplingRequestLog::record(
                $request->id,
                SamplingRequestLog::ACTION_DECLINED,
                $changes,
                $request->decline_reason
            );

            return $request;
        });

        app(SamplingEmailService::class)->requestDecided($request);

        return $request;
    }

    /**
     * Stores confirm the material is packed. Until this is set the dispatch
     * screen greys the line out as "Not ready yet".
     */
    public function markReady(SamplingRequest $request, array $data)
    {
        if (!$request->isApproved()) {
            throw ValidationException::withMessages([
                'status' => 'Only an approved request can be marked ready.',
            ]);
        }
        if ($request->is_ready) {
            throw ValidationException::withMessages([
                'status' => 'This request is already marked ready.',
            ]);
        }

        return DB::transaction(function () use ($request, $data) {
            $request->is_ready         = 1;
            $request->ready_at         = now();
            $request->ready_by_user_id = \Auth::check() ? \Auth::user()->id : null;
            $request->ready_remarks    = isset($data['ready_remarks']) ? $data['ready_remarks'] : null;
            $request->save();

            SamplingRequestLog::record(
                $request->id,
                SamplingRequestLog::ACTION_MARKED_READY,
                [],
                $request->ready_remarks
            );

            return $request;
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════════ */

    /**
     * Mirrors the old approval step: the approved qty sits in the executive's
     * pending_orders bucket until it is dispatched, at which point
     * SamplingDispatchService moves it to in_transit.
     */
    private function reserveExecutiveStock(SamplingRequest $request, $qty)
    {
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

        $stock->pending_orders = $stock->pending_orders + $qty;
        $stock->save();
    }

    /**
     * The mirror of reserveExecutiveStock(): a request declined after it was
     * approved gives its reserved qty back, so the app stops showing it as on
     * order. Nothing was ever moved to in_transit — that only happens at
     * dispatch, and a dispatched line cannot be declined at all.
     */
    private function releaseExecutiveStock(SamplingRequest $request, $qty)
    {
        if (!$request->user_id || !$request->product_id) {
            return;
        }

        $stock = FreeSamplingStock::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            return;
        }

        $stock->pending_orders = max(0, $stock->pending_orders - $qty);
        $stock->save();
    }

    /**
     * Keep the requested pack size when the product still allows it, otherwise
     * fall back to the smallest size that product can be packed in.
     */
    private function defaultPackSize($product, $requestedPackSize)
    {
        $allowed = SamplingRequest::packSizesFor($product ? $product->physical_form : null);

        if ($requestedPackSize !== null && in_array((float) $requestedPackSize, array_map('floatval', $allowed))) {
            return $requestedPackSize;
        }

        return $allowed[0];
    }

    /** A liquid can go out in 1 or 5 kg packs; a powder only in 1 kg. */
    private function assertPackSizeAllowed(SamplingRequest $request, $packSize)
    {
        $product = Product::find($request->product_id, ['id', 'physical_form']);
        $allowed = SamplingRequest::packSizesFor($product ? $product->physical_form : null);

        if (!in_array((float) $packSize, array_map('floatval', $allowed))) {
            throw ValidationException::withMessages([
                'approved_pack_size' => 'This product can only be packed in ' . implode(' or ', $allowed) . ' Kg packs.',
            ]);
        }
    }

    /** Latest dealer price effective today, mirroring the old sampling screen. */
    private function dealerPrice($productId)
    {
        if (!$productId) {
            return null;
        }

        $price = DB::table('product_pricings')
            ->where('product_id', $productId)
            ->whereDate('price_date', '<=', date('Y-m-d'))
            ->orderBy('price_date', 'DESC')
            ->value('dealer_price');

        return $price === null ? null : (float) $price;
    }

    /**
     * Turn a stored value into something the activity log can show a human.
     * Foreign keys become names; "Product: 4 → 6" is useless to a reader.
     */
    private function displayValue($field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        switch ($field) {
            case 'product_id':
                return optional(\App\Product::find($value, ['id', 'product_name']))->product_name ?: $value;
            case 'customer_id':
            case 'dispatch_customer_id':
                return optional(\App\Customer::find($value, ['id', 'name']))->name ?: $value;
            case 'dispatch_dealer_id':
                $dealer = \App\Dealer::find($value, ['id', 'business_name', 'name']);
                return $dealer ? ($dealer->business_name ?: $dealer->name) : $value;
            case 'dispatch_user_id':
                return optional(\App\User::find($value, ['id', 'name']))->name ?: $value;
            case 'approved_pack_size':
                return $value . ' Kg';
            case 'is_specific_customer':
            case 'product_known':
            case 'target_competitor':
                return $value ? 'Yes' : 'No';
        }

        return $value;
    }

    private function sameValue($old, $new)
    {
        if ($old === null || $new === null || $old === '' || $new === '') {
            return (string) $old === (string) $new;
        }
        if (is_numeric($old) && is_numeric($new)) {
            return abs((float) $old - (float) $new) < 0.0001;
        }
        return (string) $old === (string) $new;
    }

    private function nullableText(array $data, $key)
    {
        if (!isset($data[$key])) {
            return null;
        }
        $value = trim((string) $data[$key]);
        return $value === '' ? null : $value;
    }

    private function nullableNumber(array $data, $key)
    {
        if (!isset($data[$key]) || $data[$key] === '' || !is_numeric($data[$key])) {
            return null;
        }
        return $data[$key];
    }
}
