<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkNote extends Model
{
    use SoftDeletes;

    protected $table = 'work_notes';

    protected $fillable = [
        'user_id',
        'date',
        'related_to',
        'dealer_id',
        'customer_id',
        'customer_register_request_id',
        'subject',
        'activity_mode',
        'description',
        'key_take_away',
        'further_action_required',
        'action_date',
        'action_time',
        'action_remarks',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'date'                         => 'date:Y-m-d',
        'action_date'                  => 'date:Y-m-d',
        'further_action_required'      => 'integer',
        'dealer_id'                    => 'integer',
        'customer_id'                  => 'integer',
        'customer_register_request_id' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function dealer()
    {
        return $this->belongsTo(\App\Dealer::class, 'dealer_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Customer::class, 'customer_id');
    }

    public function customerRegisterRequest()
    {
        return $this->belongsTo(\App\CustomerRegisterRequest::class, 'customer_register_request_id');
    }

    /** All attachments (voice notes + files) */
    public function attachments()
    {
        return $this->hasMany(\App\WorkNoteAttachment::class, 'work_note_id');
    }

    /** Only voice note entries */
    public function voice_notes()
    {
        return $this->hasMany(\App\WorkNoteAttachment::class, 'work_note_id')
                    ->where('type', 'voice_note');
    }

    /** Only regular file attachments */
    public function files()
    {
        return $this->hasMany(\App\WorkNoteAttachment::class, 'work_note_id')
                    ->where('type', 'attachment');
    }
}