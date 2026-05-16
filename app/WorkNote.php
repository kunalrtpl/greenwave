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
        'request_date',
        'type',
        'type_other',
        'title',
        'note',
    ];

    protected $dates = ['deleted_at'];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
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