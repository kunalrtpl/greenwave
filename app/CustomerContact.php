<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $table = 'customer_contacts';

    protected $fillable = [
        'customer_id',
        'customer_register_request_id',
        'name',
        'designation',
        'mobile_number',
        'created_by',
        'department',
        'status'
    ];

    // Contact belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Creator user (optional if users table exists)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
