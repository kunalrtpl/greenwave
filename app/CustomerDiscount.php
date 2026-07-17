<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerDiscount extends Model
{
    protected $fillable = [
        'customer_id',
        'discount_type',
        'product_id',
        'from_qty',
        'to_qty',
        'committed_sale_qty',
        'dealer_share',
        'company_share',
        'moq',
        'min_qty',
        'discount',
        'special_discount',
        'net_price',
        'for_qty',
        'value',
        'applicable_type',
        'packing_type',
        // new columns added by the revamp migration
        'selling_expense_basis',
        'selling_expense_value',
        'has_special',
        'special_selling_expense_basis',
        'special_selling_expense_value',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')
                    ->where('status', 1);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}