<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerEvaluation extends Model
{
    protected $table = 'dealer_evaluations';

    protected $guarded = [];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function dealer()
    {
        return $this->belongsTo('App\Dealer', 'dealer_id');
    }

    public function answers()
    {
        return $this->hasMany('App\DealerEvaluationAnswer', 'evaluation_id')
                    ->orderBy('section_key')
                    ->orderBy('id');
    }

    public function attachments()
    {
        return $this->hasMany('App\DealerEvaluationAttachment', 'evaluation_id');
    }
}