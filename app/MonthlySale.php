<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * What a dealer's customer bought in one month, entered from the dealer app.
 *
 * @see \App\Http\Controllers\api\Dealers\MonthlySaleController
 */
class MonthlySale extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'month' => 'integer',
        'year'  => 'integer',
    ];

    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function products(){
        return $this->hasMany('App\MonthlySaleProduct');
    }

    public function created_by_dealer(){
        return $this->belongsTo('App\Dealer', 'created_by_dealer_id');
    }

    /** Sales whose month falls between two periods, both inclusive. Periods are YYYYMM integers. */
    public function scopeBetweenPeriods($query, $from, $to){
        return $query->whereRaw('(`year` * 100 + `month`) between ? and ?', [$from, $to]);
    }

    public function toApiArray(){
        return [
            'id'            => $this->id,
            'month'         => $this->month,
            'year'          => $this->year,
            'month_year'    => sprintf('%02d-%04d', $this->month, $this->year),
            'dealer_id'     => $this->dealer_id,
            'dealer_name'   => optional($this->dealer)->business_name,
            'customer_id'   => $this->customer_id,
            'customer_name' => optional($this->customer)->name,
            'remarks'       => $this->remarks,
            'total_qty'     => round($this->products->sum('qty'), 3),
            'products'      => $this->products->map->toApiArray()->values(),
            'created_by_dealer_id'   => $this->created_by_dealer_id,
            'created_by_dealer_name' => optional($this->created_by_dealer)->owner_name,
            'created_at'    => optional($this->created_at)->toDateTimeString(),
            'updated_at'    => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
