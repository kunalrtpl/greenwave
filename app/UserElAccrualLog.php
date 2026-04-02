<?php
// App/UserElAccrualLog.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserElAccrualLog extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'financial_year',
        'accrual_month',
        'accrual_year',
        'days_added',
        'balance_before',
        'balance_after',
        'cap_applied',
        'source',
        'notes',
    ];

    protected $casts = [
        'days_added'     => 'float',
        'balance_before' => 'float',
        'balance_after'  => 'float',
        'cap_applied'    => 'float',
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