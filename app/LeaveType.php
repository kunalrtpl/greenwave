<?php
// ─────────────────────────────────────────────
// App/LeaveType.php
// ─────────────────────────────────────────────
namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name', 'code', 'has_quota', 'quota_editable',
        'default_quota', 'color', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'has_quota'      => 'boolean',
        'quota_editable' => 'boolean',
        'is_active'      => 'boolean',
        'default_quota'  => 'float',
    ];

    public function quotas()
    {
        return $this->hasMany(UserLeaveQuota::class);
    }
}