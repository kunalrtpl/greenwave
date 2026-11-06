<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** One product on a monthly sale. */
class MonthlySaleProduct extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['qty' => 'float'];

    public function monthly_sale(){
        return $this->belongsTo('App\MonthlySale');
    }

    public function product(){
        return $this->belongsTo('App\Product');
    }

    public function toApiArray(){
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => optional($this->product)->product_name,
            'product_code' => optional($this->product)->product_code,
            'qty'          => (float) $this->qty,
        ];
    }
}
