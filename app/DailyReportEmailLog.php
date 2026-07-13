<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyReportEmailLog extends Model
{
    protected $table = 'daily_report_email_logs';
    protected $guarded = [];

    protected $casts = [
        'user_id'  => 'integer',
        'attempts' => 'integer',
    ];

    protected $dates = ['sent_at'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
