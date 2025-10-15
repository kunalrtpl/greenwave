<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'date', 'status', 'remarks','secondary_status','leave_time'];
}
