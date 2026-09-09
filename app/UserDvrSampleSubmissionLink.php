<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Sample submissions attached to a DVR.
 *
 * Replaces the single user_dvrs.sample_submission_id column, which is kept for
 * historical rows but is no longer written to or read from.
 */
class UserDvrSampleSubmissionLink extends Model
{
    protected $table = 'user_dvr_sample_submission_links';
    protected $guarded = [];

    public function sample_submission_info()
    {
        return $this->belongsTo('App\SampleSubmission', 'sample_submission_id')
            ->with('product');
    }

    public function dvr()
    {
        return $this->belongsTo(UserDvr::class, 'user_dvr_id');
    }
}
