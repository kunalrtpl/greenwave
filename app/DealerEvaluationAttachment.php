<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerEvaluationAttachment extends Model
{
    protected $table = 'dealer_evaluation_attachments';

    protected $fillable = ['evaluation_id', 'file', 'original_name', 'mime_type'];

    public function evaluation()
    {
        return $this->belongsTo('App\DealerEvaluation', 'evaluation_id');
    }
}