<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * A trial attached to a DVR for a secondary purpose (feedback taken on an old
 * trial, or a trial report discussed during the visit) as opposed to the trials
 * actually carried out on the DVR, which live in user_dvr_trial_links.
 */
class UserDvrAdditionalTrialLink extends Model
{
    protected $table = 'user_dvr_additional_trial_links';
    protected $guarded = [];

    /** Allowed values for the `type` column */
    const TYPE_TRIAL_FEEDBACK = 'trial_feedback';
    const TYPE_TRIAL_REPORT_SUBMISSION_DISCUSSION = 'trial_report_submission_discussion';

    public static function types()
    {
        return [
            self::TYPE_TRIAL_FEEDBACK,
            self::TYPE_TRIAL_REPORT_SUBMISSION_DISCUSSION,
        ];
    }

    public function trial_info()
    {
        return $this->belongsTo('App\Trial', 'trial_id')
            ->select('id', 'trial_number', 'trial_type', 'objective', 'status', 'trial_done', 'customer_id', 'created_at');
    }

    public function dvr()
    {
        return $this->belongsTo(UserDvr::class, 'user_dvr_id');
    }
}
