<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerPriceRequest extends Model
{
    protected $table = 'customer_price_requests';

    protected $fillable = [
        'user_id', 'customer_id', 'product_id',
        'payment_term', 'freight_basis', 'freight',
        'packing_size', 'final_msp', 'final_customer_price',
        'selling_expense_basis', 'selling_expense_value',
        'selling_expenses', 'additional_realization',
        'status', 'reject_reason', 'action_by', 'action_at', 'customer_discount_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Customer::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function action_user()
    {
        return $this->belongsTo(\App\User::class, 'action_by');
    }
}