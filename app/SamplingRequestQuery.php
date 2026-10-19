<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One message in the thread hanging off a sample request.
 *
 * `sender_type` says which side wrote it, and the two read flags let each side
 * count what it has not seen: the admin's unread are the employee's messages
 * with admin_read = 0, and the other way round for the app.
 */
class SamplingRequestQuery extends Model
{
    const SENDER_ADMIN    = 'admin';
    const SENDER_EMPLOYEE = 'employee';

    protected $fillable = [
        'sampling_request_id',
        'sender_id',
        'sender_type',
        'message',
        'admin_read',
        'user_read',
    ];

    protected $casts = [
        'admin_read' => 'boolean',
        'user_read'  => 'boolean',
    ];

    public function sampling_request()
    {
        return $this->belongsTo('App\SamplingRequest');
    }

    public function sender()
    {
        return $this->belongsTo('App\User', 'sender_id');
    }

    /* ─────────────────────────── Scopes ─────────────────────────── */

    /** Messages the admin has not opened yet — written by the employee. */
    public function scopeUnreadByAdmin($query)
    {
        return $query->where('sender_type', self::SENDER_EMPLOYEE)->where('admin_read', 0);
    }

    /** Messages the employee has not opened yet — written by the admin. */
    public function scopeUnreadByEmployee($query)
    {
        return $query->where('sender_type', self::SENDER_ADMIN)->where('user_read', 0);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** The shape both the admin screen and the app expect for one message. */
    public function toThreadArray()
    {
        return [
            'id'          => $this->id,
            'message'     => $this->message,
            'sender_type' => $this->sender_type,
            'sender_id'   => $this->sender_id,
            'sender_name' => optional($this->sender)->name,
            'admin_read'  => (bool) $this->admin_read,
            'user_read'   => (bool) $this->user_read,
            'created_at'  => $this->created_at ? $this->created_at->format('d M Y, h:i A') : null,
            'created_ago' => $this->created_at ? $this->created_at->diffForHumans() : null,
        ];
    }
}
