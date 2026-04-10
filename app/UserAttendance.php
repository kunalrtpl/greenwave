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
        // Audit — admin status changes
        'previous_status',
        'status_changed_by',
        'status_change_note',
        'status_changed_at',
    ];

    protected $casts = [
        'missed'             => 'boolean',
        'in_latitude'        => 'float',
        'in_longitude'       => 'float',
        'out_latitude'       => 'float',
        'out_longitude'      => 'float',
        'status_changed_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function leaves()
    {
        return $this->hasMany(UserLeave::class, 'attendance_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(\App\User::class, 'status_changed_by');
    }

    // ── Computed attributes ───────────────────────────────────────

    public function getIsOutMarkedAttribute(): bool
    {
        return !is_null($this->out_time);
    }

    public function getIsOpenAttribute(): bool
    {
        return !is_null($this->in_time)
            && is_null($this->out_time)
            && !$this->missed;
    }

    public function getWasAdminUpdatedAttribute(): bool
    {
        return !is_null($this->status_changed_by);
    }

    protected $appends = [
        'is_out_marked',
        'is_open',
        'was_admin_updated',
    ];
}