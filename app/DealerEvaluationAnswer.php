<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerEvaluationAnswer extends Model
{
    protected $table = 'dealer_evaluation_answers';

    protected $fillable = [
        'evaluation_id',
        'section_key',
        'section_name',
        'question_key',
        'question_text',
        'question_type',
        'available_options',
        'selected_options',
        'custom_answer',
    ];

    protected $casts = [
        'available_options' => 'array',
        'selected_options'  => 'array',
    ];
}