<?php
// App/UserWeeklyOffCompensation.php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserWeeklyOffCompensation extends Model
{
    protected $table = "user_weekly_off_compensations";
    protected $fillable = [
        'user_id', 'worked_date', 'valid_from',
        'expires_on', 'used_on', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    // Auto-compute status based on dates
    public function getIsExpiredAttribute()
    {
        return $this->status === 'available' &&
               Carbon::today()->gt(Carbon::parse($this->expires_on));
    }

    protected $appends = ['is_expired'];
}