<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MonthlyCustomerVisitAnalysisLog extends Model
{
    protected $table = 'monthly_customer_visit_analysis_logs';

    protected $guarded = [];

    protected $dates = ['sent_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
