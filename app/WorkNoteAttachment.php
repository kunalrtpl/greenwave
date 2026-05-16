<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkNoteAttachment extends Model
{
    protected $table = 'work_note_attachments';

    protected $fillable = [
        'work_note_id',
        'type',             // 'voice_note' | 'attachment'
        'file',
        'original_name',
        'mime_type',
        'duration_seconds',
    ];

    public function work_note()
    {
        return $this->belongsTo(\App\WorkNote::class, 'work_note_id');
    }
}