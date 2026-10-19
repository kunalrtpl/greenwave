<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One requested product of a sample request (SR-1.1, SR-1.2, …).
 *
 * Lifecycle, driven entirely by `status`:
 *
 *   pending   default; waiting for the admin to approve or decline
 *   approved  approved_qty is set and the line is waiting to be dispatched
 *   closed    total_dispatched_qty has reached approved_qty
 *   declined  refused with a reason; never dispatchable
 *
 * `is_ready` is a flag inside `approved`, not a status of its own: the stores
 * team marks a line ready so the dispatch screen knows which lines can be
 * picked today.
 */
class SamplingRequest extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CLOSED   = 'closed';
    const STATUS_DECLINED = 'declined';

    const STATUSES = [
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_APPROVED => 'Approved (Pending Dispatch)',
        self::STATUS_CLOSED   => 'Closed',
        self::STATUS_DECLINED => 'Declined',
    ];

    /**
     * Pack sizes a product can be given out in, by physical form — the same
     * rule the old sampling screen used. Liquids go out in 1 or 5 kg packs,
     * everything else only in 1 kg.
     */
    const PACK_SIZES = [
        'liquid'  => [1, 5],
        'default' => [1],
    ];

    /** @return array of allowed pack sizes for a product's physical form */
    public static function packSizesFor($physicalForm)
    {
        $form = strtolower((string) $physicalForm);

        return isset(self::PACK_SIZES[$form]) ? self::PACK_SIZES[$form] : self::PACK_SIZES['default'];
    }

    protected $guarded = ['id'];

    protected $casts = [
        'requested_qty'        => 'float',
        'approved_qty'         => 'float',
        'approved_value'       => 'float',
        'dealer_price'         => 'float',
        'total_dispatched_qty' => 'float',
        'pack_size'            => 'float',
        'approved_pack_size'   => 'float',
        'is_ready'             => 'boolean',
        'is_voluntarily'       => 'boolean',
        'is_specific_customer' => 'boolean',
        'product_known'        => 'boolean',
        'target_competitor'    => 'boolean',
    ];

    protected $dates = ['request_date', 'approved_at', 'declined_at', 'ready_at', 'closed_at'];

    protected $appends = ['pending_qty', 'status_label'];


    /* ─────────────────────────── Relations ─────────────────────────── */

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    /** What the field user asked for — kept even after the admin swaps the product. */
    public function requested_product(){
        return $this->belongsTo('App\Product', 'requested_product_id');
    }

    /** What was finally approved. */
    public function product(){
        return $this->belongsTo('App\Product', 'product_id');
    }

    public function dispatch_dealer(){
        return $this->belongsTo('App\Dealer', 'dispatch_dealer_id');
    }

    public function dispatch_customer(){
        return $this->belongsTo('App\Customer', 'dispatch_customer_id');
    }

    public function dispatch_user(){
        return $this->belongsTo('App\User', 'dispatch_user_id');
    }

    public function approved_by(){
        return $this->belongsTo('App\User', 'approved_by_user_id');
    }

    public function declined_by(){
        return $this->belongsTo('App\User', 'declined_by_user_id');
    }

    public function ready_by(){
        return $this->belongsTo('App\User', 'ready_by_user_id');
    }

    public function dispatches(){
        return $this->hasMany('App\SamplingDispatch');
    }

    public function logs(){
        return $this->hasMany('App\SamplingRequestLog')->orderBy('id', 'DESC');
    }

    /** The query thread between the employee and the admin, oldest first. */
    public function queries(){
        return $this->hasMany('App\SamplingRequestQuery')->orderBy('id', 'ASC');
    }

    /** Every product line posted in the same request, including this one. */
    public function scopeOfGroup($query, $groupNo){
        return $query->where('request_group_no', $groupNo);
    }

    /* ─────────────────────────── Scopes ─────────────────────────── */

    public function scopeOfStatus($query, $status){
        return $query->where('status', $status);
    }

    /** Approved lines that still have qty left to send. */
    public function scopePendingDispatch($query){
        return $query->where('status', self::STATUS_APPROVED)
            ->whereColumn('total_dispatched_qty', '<', 'approved_qty');
    }

    /* ─────────────────────────── Accessors ─────────────────────────── */

    /** Approved but not yet sent. Zero until the line is approved. */
    public function getPendingQtyAttribute(){
        if ($this->approved_qty === null) {
            return 0;
        }
        return max(0, round($this->approved_qty - $this->total_dispatched_qty, 3));
    }

    /** "SR-1" — the request the line belongs to, without the product suffix. */
    public function getGroupReferenceAttribute(){
        return 'SR-' . $this->request_group_no;
    }

    /** Unique key for grouping lines of one request in a listing. */
    public function getGroupKeyAttribute(){
        return (string) $this->request_group_no;
    }

    /** Short status for email subjects — "Approved (Pending Dispatch)" is too long. */
    public function getEmailStatusAttribute(){
        return ucfirst((string) $this->status);
    }

    public function getStatusLabelAttribute(){
        return isset(self::STATUSES[$this->status]) ? self::STATUSES[$this->status] : ucfirst((string) $this->status);
    }

    /**
     * One word, for a chip in a listing. "Approved (Pending Dispatch)" is the
     * full label and is far too wide for a table column — the Ready / Not
     * ready chip beside it already says where an approved line stands.
     */
    public function getStatusShortAttribute(){
        return ucfirst((string) $this->status);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** Pack sizes the currently selected product may be packed in. */
    public function allowedPackSizes(){
        return self::packSizesFor(optional($this->product)->physical_form);
    }

    /**
     * A thread is open while the request is still live. Once it is closed or
     * declined the history stays readable but nothing more can be posted.
     */
    public function acceptsQueries(){
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED], true);
    }

    /** Why posting is shut, for the screen and the API to say the same thing. */
    public function queriesClosedReason(){
        if ($this->acceptsQueries()) {
            return null;
        }
        return 'This request is ' . strtolower($this->status_label)
             . ', so the query thread is closed. You can still read the history.';
    }

    public function isPending(){
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(){
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDispatchable(){
        return $this->isApproved() && $this->pending_qty > 0;
    }

    /**
     * The admin may refuse a request at any stage until the material moves —
     * while pending, after approval and after it has been marked ready. Once
     * any qty has been dispatched it is too late: that material is in the
     * field and no decline can call it back.
     */
    public function canBeDeclined(){
        return ($this->isPending() || $this->isApproved()) && $this->total_dispatched_qty <= 0;
    }

    /** Who the sample is answerable to — a dealer, otherwise the executive. */
    public function ownerName(){
        if ($this->dealer_id) {
            return optional($this->dealer)->business_name ?: optional($this->dealer)->name;
        }
        return optional($this->user)->name;
    }

    /** "Dealer" or "Employee", for labelling a row. */
    public function ownerType(){
        return $this->dealer_id ? 'Dealer' : 'Employee';
    }

    /**
     * One request the way the executive app lists it.
     *
     * Every number the app needs to show progress is here: what was asked
     * for, what the admin approved (which may be a different product or a
     * smaller qty), what has gone out and what is still pending against it.
     *
     * @param array $queryCounts ['total' => n, 'unread' => n] for this row
     */
    public function toApiArray(array $queryCounts = []){
        return [
            'id'              => $this->id,
            'reference_no'    => $this->reference_no,
            'group_reference' => $this->group_reference,
            'line_no'         => $this->line_no,
            'request_date'    => $this->request_date ? $this->request_date->format('Y-m-d') : null,
            'sample_type'     => $this->sample_type,
            'is_voluntarily'  => (bool) $this->is_voluntarily,

            /* The status the app badges the row with */
            'status'       => $this->status,
            'status_label' => $this->status_label,

            /* What the app sent — never changed afterwards */
            'requested' => [
                'product_id'     => $this->requested_product_id,
                'product_name'   => optional($this->requested_product)->product_name,
                'product_code'   => optional($this->requested_product)->product_code,
                'pack_size_info' => $this->pack_size_info,
                'pack_size'      => $this->pack_size,
                'no_of_packs'    => $this->no_of_packs,
                'qty'            => (float) $this->requested_qty,
                'remarks'        => $this->item_remarks,
            ],

            /* What the admin authorised — product included, it can be swapped */
            'approved' => [
                'product_id'   => $this->product_id,
                'product_name' => optional($this->product)->product_name,
                'product_code' => optional($this->product)->product_code,
                'pack_size'    => $this->approved_pack_size,
                'no_of_packs'  => $this->approved_no_of_packs,
                'qty'          => $this->approved_qty === null ? null : (float) $this->approved_qty,
                'dealer_price' => $this->dealer_price === null ? null : (float) $this->dealer_price,
                'value'        => $this->approved_value === null ? null : (float) $this->approved_value,
                'remarks'      => $this->approval_remarks,
                'approved_at'  => $this->approved_at ? $this->approved_at->format('Y-m-d H:i:s') : null,
                'approved_by'  => optional($this->approved_by)->name,
            ],
            'product_changed' => (bool) ($this->requested_product_id && $this->product_id
                                 && $this->requested_product_id != $this->product_id),

            /* Progress, in kg */
            'qty' => [
                'requested'  => (float) $this->requested_qty,
                'approved'   => $this->approved_qty === null ? null : (float) $this->approved_qty,
                'dispatched' => (float) $this->total_dispatched_qty,
                'pending'    => $this->pending_qty,
            ],

            /* Why it was refused, if it was */
            'decline_reason' => $this->decline_reason,
            'declined_at'    => $this->declined_at ? $this->declined_at->format('Y-m-d H:i:s') : null,

            /* Ready to go out — a flag inside approved, not a status */
            'is_ready' => (bool) $this->is_ready,
            'ready_at' => $this->ready_at ? $this->ready_at->format('Y-m-d H:i:s') : null,

            'closed_at' => $this->closed_at ? $this->closed_at->format('Y-m-d H:i:s') : null,

            'customer_id'   => $this->customer_id,
            'customer_name' => optional($this->customer)->name,

            'purpose_reason_for_request' => $this->purpose_reason_for_request,
            'application_plan'           => $this->application_plan,

            'dispatch_to'      => $this->dispatch_to,
            'dispatch_party'   => $this->dispatchPartyName(),
            'dispatch_address' => $this->dispatch_address,

            /* What has actually gone out against it, and whether it arrived */
            'dispatches' => $this->relationLoaded('dispatches')
                ? $this->dispatches->map(function ($d) { return $d->toApiArray(); })->values()
                : [],
            'receipt'    => $this->receiptSummary(),

            /* So the app can badge the query thread without a second call */
            'queries' => [
                'total'  => isset($queryCounts['total']) ? (int) $queryCounts['total'] : 0,
                'unread' => isset($queryCounts['unread']) ? (int) $queryCounts['unread'] : 0,
                'open'   => $this->acceptsQueries(),
            ],

            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /** How much of what went out has been confirmed as received. */
    public function receiptSummary(){
        if (!$this->relationLoaded('dispatches')) {
            return ['lines' => 0, 'received_lines' => 0, 'pending_lines' => 0, 'all_received' => false];
        }

        $lines    = $this->dispatches->count();
        $received = $this->dispatches->where('is_received', true)->count();

        return [
            'lines'          => $lines,
            'received_lines' => $received,
            'pending_lines'  => $lines - $received,
            'all_received'   => $lines > 0 && $received === $lines,
        ];
    }

    /** "Dispatch To" as a human line, whichever of the three party columns is set. */
    public function dispatchPartyName(){
        if ($this->dispatch_dealer_id) {
            return optional($this->dispatch_dealer)->business_name ?: optional($this->dispatch_dealer)->name;
        }
        if ($this->dispatch_customer_id) {
            return optional($this->dispatch_customer)->name;
        }
        return optional($this->dispatch_user)->name;
    }
}
