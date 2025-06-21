<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestOtp extends Model
{
    //
    protected $fillable = ['module', 'mobile', 'otp'];
}
