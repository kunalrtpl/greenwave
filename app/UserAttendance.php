<?php
// App/UserAttendance.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAttendance extends Model
{
    protected $fillable = [
        // IN
        'user_id', 'in_date', 'in_time',
        'in_latitude', 'in_longitude', 'in_latitude_longitude_address',
        'in_place_of_attendance', 'in_other',
        'in_customer_id', 'in_customer_register_request_id', 'in_dealer_id',
        // OUT
        'out_date', 'out_time',
        'out_latitude', 'out_longitude', 'out_latitude_longitude_address',
        'out_place_of_attendance', 'out_other',
        'out_customer_id', 'out_customer_register_request_id', 'out_dealer_id',
        'missed',
        // Status
        'status',
    ];

    protected $casts = [
        'missed'        => 'boolean',
        'in_latitude'   => 'float',
        'in_longitude'  => 'float',
        'out_latitude'  => 'float',
        'out_longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function leaves()
    {
        return $this->hasMany(UserLeave::class, 'attendance_id');
    }

    // Check if out is marked
    public function getIsOutMarkedAttribute()
    {
        return !is_null($this->out_time);
    }

    protected $appends = ['is_out_marked'];
}