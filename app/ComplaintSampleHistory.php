<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplaintSampleHistory extends Model
{
    //
    public function userinfo(){
        return $this->belongsto('App\User','user_id','id');
    }

    public function dealerinfo(){
        return $this->belongsto('App\Dealer','dealer_id','id');
    }
}
