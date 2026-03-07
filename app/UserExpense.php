<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserExpense extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'expense_date', 'requested_amount','approved_amount',
        'travel_km', 'charge_per_km', 'is_intercity', 'intercity_route',
        'remarks', 'image', 'status','verified_by','approved_by'
    ];

    public function category()
    {
        return $this->belongsTo('App\ExpenseCategory', 'category_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Accessor to provide the full URL for the image
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Using the path logic from your Controller's save method
        return asset('ExpenseReceipts/' . $this->user_id . '/' . $value);
    }
}