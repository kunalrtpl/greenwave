<?php
// App/UserLeave.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLeave extends Model
{
    protected $fillable = [
        'user_id', 'leave_type_id', 'date',
        'leave_duration', 'half_day_type',
        'remarks', 'status', 'attendance_id', 'quota_deducted'
    ];

    protected $casts = [
        'quota_deducted' => 'float',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function attendance()
    {
        return $this->belongsTo(UserAttendance::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}