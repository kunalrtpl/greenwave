<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLinkedDealer extends Model
{
    protected $fillable = ['user_id', 'dealer_id'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }
}
