<?php
// App/UserLeaveQuota.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLeaveQuota extends Model
{
    protected $fillable = [
        'user_id', 'leave_type_id', 'financial_year',
        'total_quota', 'used_quota'
    ];

    protected $casts = [
        'total_quota' => 'float',
        'used_quota'  => 'float',
    ];

    // Computed: always fresh remaining
    public function getRemainingQuotaAttribute()
    {
        return max(0, $this->total_quota - $this->used_quota);
    }

    protected $appends = ['remaining_quota'];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}