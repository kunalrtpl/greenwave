<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Dealer;

class CustomerRegisterRequest extends Model
{

    protected $appends = ['business_card_url','business_card_two_url'];

    public function getBusinessCardUrlAttribute()
    {
        if ($this->business_card) {
            return asset($this->business_card);
        }
        return null; // or default image url if you want
    }

    public function getBusinessCardTwoUrlAttribute()
    {
        if ($this->business_card_two) {
            return asset($this->business_card_two);
        }
        return null; // or default image url if you want
    }


    // Relationship: who created the request
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship: linked dealer
    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id');
    }

    // Relationship: linked executive (user)
    public function linkedExecutive()
    {
        return $this->belongsTo(User::class, 'linked_executive');
    }
}
