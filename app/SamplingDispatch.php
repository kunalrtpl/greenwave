<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One dispatched line. Rows sharing a dispatch_ref_no went out on one challan.
 *
 * `is_received` / `received_at` are the employee's side of it: dispatching
 * only puts the material in transit, and the app confirms receipt, which is
 * what moves their in-transit stock into stock in hand.
 */
class SamplingDispatch extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'dispatch_qty' => 'float',
        'is_received'  => 'boolean',
    ];

    protected $dates = ['dispatch_date', 'invoice_date', 'received_at'];

    public function sampling_request(){
        return $this->belongsTo('App\SamplingRequest');
    }

    public function product(){
        return $this->belongsTo('App\Product');
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

    public function dispatched_by(){
        return $this->belongsTo('App\User', 'dispatched_by_user_id');
    }

    /* ─────────────────────────── Scopes ─────────────────────────── */

    /** Still on its way — the employee has not confirmed it yet. */
    public function scopeNotReceived($query){
        return $query->where('is_received', 0);
    }

    public function scopeReceived($query){
        return $query->where('is_received', 1);
    }

    /**
     * Everything one employee is answerable for: material addressed to them,
     * and material sent out against a request they raised — a sample sent
     * straight to their customer is still theirs to confirm, because it is
     * their in-transit stock that it came out of.
     */
    public function scopeForEmployee($query, $userId){
        return $query->where(function ($q) use ($userId) {
            $q->where('dispatch_user_id', $userId)
              ->orWhereHas('sampling_request', function ($r) use ($userId) {
                  $r->where('user_id', $userId);
              });
        });
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    public function dispatchPartyName(){
        if ($this->dispatch_dealer_id) {
            return optional($this->dispatch_dealer)->business_name ?: optional($this->dispatch_dealer)->name;
        }
        if ($this->dispatch_customer_id) {
            return optional($this->dispatch_customer)->name;
        }
        return optional($this->dispatch_user)->name;
    }

    /**
     * One dispatched line the way the app shows it: what arrived, on which
     * challan, and whether it has been confirmed.
     */
    public function toApiArray(){
        $request = $this->sampling_request;

        return [
            'id'              => $this->id,
            'dispatch_ref_no' => $this->dispatch_ref_no,
            'dispatch_date'   => $this->dispatch_date ? $this->dispatch_date->format('Y-m-d') : null,

            'sampling_request_id' => $this->sampling_request_id,
            'reference_no'        => optional($request)->reference_no,
            'is_voluntarily'      => (bool) optional($request)->is_voluntarily,

            'product_id'   => $this->product_id,
            'product_name' => optional($this->product)->product_name,
            'product_code' => optional($this->product)->product_code,
            'batch_no'     => $this->batch_no,
            'dispatch_qty' => (float) $this->dispatch_qty,

            /* Challan — shared by every line of the same dispatch_ref_no */
            'invoice_no'   => $this->invoice_no,
            'invoice_date' => $this->invoice_date ? $this->invoice_date->format('Y-m-d') : null,
            'transport'    => $this->transport,
            'lr_no'        => $this->lr_no,
            'remarks'      => $this->remarks,

            /* Where it went */
            'dispatch_to'      => $this->dispatch_to,
            'dispatch_party'   => $this->dispatchPartyName(),
            'dispatch_address' => $this->dispatch_address,

            /* Receipt */
            'is_received' => (bool) $this->is_received,
            'received_at' => $this->received_at ? $this->received_at->format('Y-m-d H:i:s') : null,
            'status'      => $this->is_received ? 'received' : 'in_transit',
            'status_label' => $this->is_received ? 'Received' : 'In Transit',
        ];
    }
}
