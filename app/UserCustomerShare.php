<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCustomerShare extends Model
{
    //
    public function customer(){
    	return $this->belongsto('App\Customer');
    }

    public function user(){
    	return $this->belongsto('App\User');
    }
}
