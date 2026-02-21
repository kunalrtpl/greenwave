<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerDiscount extends Model
{

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')
                    ->where('status', 1);
    }
}