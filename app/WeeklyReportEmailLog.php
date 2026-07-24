<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeeklyReportEmailLog extends Model
{
    protected $table = 'weekly_report_email_logs';

    protected $guarded = [];

    protected $dates = ['sent_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
