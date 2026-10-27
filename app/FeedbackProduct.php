<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Products attached to a feedback.
 *
 * Replaces the single feedback.product_id column so a feedback can have
 * multiple products.
 */
class FeedbackProduct extends Model
{
    protected $table = 'feedback_products';
    protected $guarded = [];

    public function feedback()
    {
        return $this->belongsTo('App\Feedback', 'feedback_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }
}
