<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDvrCustomerContact extends Model
{
    protected $table = 'user_dvr_customer_contacts';

    //  IMPORTANT FIX
    protected $fillable = [
        'user_dvr_id',
        'customer_contact_id'
    ];

    public function dvr()
    {
        return $this->belongsTo(UserDvr::class, 'user_dvr_id');
    }

    public function customerContact(){
        return $this->belongsTo('App\CustomerContact','customer_contact_id');
    }
}
