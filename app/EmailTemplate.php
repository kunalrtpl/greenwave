<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'event_key',
        'name',
        'subject',
        'blade_view',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'is_active',
    ];

    protected $casts = [
        'to_emails'  => 'array',
        'cc_emails'  => 'array',
        'bcc_emails' => 'array',
        'is_active'  => 'boolean',
    ];

    public static function getActive($eventKey)
    {
        return static::where('event_key', $eventKey)
                     ->where('is_active', true)
                     ->first();
    }
}