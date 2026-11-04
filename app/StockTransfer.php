<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Free-sample stock offered by one marketing employee to another.
 *
 * The sender raises it, the receiver settles it. While it is pending the qty
 * is blocked on the sender's stock row, so it can be offered only once.
 *
 *   pending    waiting for the receiving employee
 *   approved   accepted; the stock is now the receiver's
 *   declined   refused, with a reason; the stock went back to the sender
 *   cancelled  withdrawn by the sender before it was decided
 */
class StockTransfer extends Model
{
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_DECLINED  = 'declined';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING   => 'Pending',
        self::STATUS_APPROVED  => 'Approved',
        self::STATUS_DECLINED  => 'Declined',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $guarded = ['id'];

    protected $dates = ['transfer_date', 'approved_at', 'declined_at', 'cancelled_at'];

    protected $appends = ['status_label'];

    /* ─────────────────────────── Relations ─────────────────────────── */

    /** The employee giving the stock. */
    public function from_user(){
        return $this->belongsTo('App\User', 'from_user_id');
    }

    /** The employee being offered it. */
    public function to_user(){
        return $this->belongsTo('App\User', 'to_user_id');
    }

    public function items(){
        return $this->hasMany('App\StockTransferItem')->orderBy('id');
    }

    /* ─────────────────────────── Scopes ─────────────────────────── */

    public function scopeOfStatus($query, $status){
        return $query->where('status', $status);
    }

    /** Waiting on this employee to accept or refuse. */
    public function scopeAwaiting($query, $userId){
        return $query->where('to_user_id', $userId)->where('status', self::STATUS_PENDING);
    }

    /** Everything this employee is part of, either way round. */
    public function scopeInvolving($query, $userId){
        return $query->where(function ($q) use ($userId) {
            $q->where('from_user_id', $userId)->orWhere('to_user_id', $userId);
        });
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    public function getStatusLabelAttribute(){
        return isset(self::STATUSES[$this->status]) ? self::STATUSES[$this->status] : ucfirst((string) $this->status);
    }

    public function isPending(){
        return $this->status === self::STATUS_PENDING;
    }

    /** Only the receiver settles it, and only while it is pending. */
    public function canBeDecidedBy($userId){
        return $this->isPending() && (int) $this->to_user_id === (int) $userId;
    }

    /** The sender may withdraw it right up until it is settled. */
    public function canBeCancelledBy($userId){
        return $this->isPending() && (int) $this->from_user_id === (int) $userId;
    }

    /** Total kg on the transfer. */
    public function totalQty(){
        return round($this->items->sum('qty'), 3);
    }

    /**
     * One transfer the way the app lists it.
     *
     * `direction` is from the caller's point of view, so the app can show
     * "to Girish" or "from Venkata" without working it out itself.
     */
    public function toApiArray($forUserId = null){
        $isSender = $forUserId !== null && (int) $this->from_user_id === (int) $forUserId;

        return [
            'id'            => $this->id,
            'reference_no'  => $this->reference_no,
            'transfer_date' => $this->transfer_date ? $this->transfer_date->format('Y-m-d') : null,

            'status'       => $this->status,
            'status_label' => $this->status_label,

            'direction' => $forUserId === null ? null : ($isSender ? 'sent' : 'received'),

            'from_user_id'   => $this->from_user_id,
            'from_user_name' => optional($this->from_user)->name,
            'to_user_id'     => $this->to_user_id,
            'to_user_name'   => optional($this->to_user)->name,

            'remarks'        => $this->remarks,
            'decline_reason' => $this->decline_reason,

            'approved_at'  => $this->approved_at ? $this->approved_at->format('Y-m-d H:i:s') : null,
            'declined_at'  => $this->declined_at ? $this->declined_at->format('Y-m-d H:i:s') : null,
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->format('Y-m-d H:i:s') : null,

            'total_qty' => $this->relationLoaded('items') ? $this->totalQty() : null,
            'items'     => $this->relationLoaded('items')
                ? $this->items->map(function ($item) { return $item->toApiArray(); })->values()
                : [],

            // What this caller may do with it right now.
            'can_approve' => $forUserId !== null && $this->canBeDecidedBy($forUserId),
            'can_decline' => $forUserId !== null && $this->canBeDecidedBy($forUserId),
            'can_cancel'  => $forUserId !== null && $this->canBeCancelledBy($forUserId),

            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
