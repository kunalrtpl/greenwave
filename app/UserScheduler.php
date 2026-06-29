<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserScheduler extends Model
{
    protected $table = 'user_schedulers';

    protected $fillable = [
        'user_id',
        'related_to',
        'dealer_id',
        'customer_id',
        'customer_register_request_id',
        'other_customer_name',
        'subject',
        'dvr_id',
        'user_dvr_id',
        'previous_scheduler_id',
        'next_scheduler_id',
        'scheduler_date',
        'scheduler_time',
        'description',
        'status',
    ];

    protected $casts = [
        'dealer_id'                    => 'integer',
        'customer_id'                  => 'integer',
        'customer_register_request_id' => 'integer',
        'dvr_id'                       => 'integer',
        'user_dvr_id'                  => 'integer',
        'previous_scheduler_id'        => 'integer',
        'next_scheduler_id'            => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function dealer()
    {
        return $this->belongsTo(\App\Dealer::class, 'dealer_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Customer::class, 'customer_id');
    }

    public function customer_register_request()
    {
        return $this->belongsTo(\App\CustomerRegisterRequest::class, 'customer_register_request_id')
                    ->with(['dealer', 'linkedExecutive']);
    }

    public function userDvr()
    {
        return $this->belongsTo(\App\UserDvr::class, 'user_dvr_id');
    }

    /** @deprecated use userDvr() — kept for old API backward compatibility */
    public function dvr()
    {
        return $this->belongsTo(\App\Dvr::class, 'dvr_id');
    }

    public function previous_scheduler()
    {
        return $this->belongsTo(\App\UserScheduler::class, 'previous_scheduler_id')
                    ->with(['customer', 'customer_register_request']);
    }

    public function next_scheduler()
    {
        return $this->belongsTo(\App\UserScheduler::class, 'next_scheduler_id')
                    ->with(['customer', 'customer_register_request']);
    }
}