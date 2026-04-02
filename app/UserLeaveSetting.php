<?php
// App/UserLeaveSetting.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLeaveSetting extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'financial_year',
        'annual_quota',
        'monthly_accrual',
        'carry_forward',
        'carry_forward_limit',
    ];

    protected $casts = [
        'annual_quota'        => 'float',
        'monthly_accrual'     => 'float',
        'carry_forward'       => 'boolean',
        'carry_forward_limit' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}