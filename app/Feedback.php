<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    //
    public function dealer(){
    	return $this->belongsto('App\Dealer','dealer_id','id');
    }
    public function customer(){
    	return $this->belongsto('App\Customer','customer_id','id');
    }

    public function customer_employee(){
        return $this->belongsto('App\CustomerEmployee','customer_employee_id','id');
    }

    public function product(){
    	return $this->belongsto('App\Product','product_id','id');
    }

    public function replies(){
        return $this->hasMany('App\FeedbackReply')->orderby('id','DESC')->with('reply_by');
    }

    public static function feedbackHistories($data){
        $feedbackId = $data['feedback_id'];

        $replies = \App\FeedbackReply::with('reply_by')->where('feedback_id',$feedbackId)->get();

        $dvrs = \App\Dvr::with(['customer','products','customer_register_request','complaint_info','query_info','other_team_member_info','trial_report_info','complaint_sample','market_sample','sample_submission','user_scheduler','trial_reports','user'])->where('complaint_id',$feedbackId)->get();

        $complaint_samples = \App\ComplaintSample::with(['customer','productinfo','feedback','histories','user','dealer'])->where('feedback_id',$feedbackId)->get();

        $sample_submissions = \App\SampleSubmission::with(['customer','product','complaint_info','user','dealer'])->where('complaint_id',$feedbackId)->get();

        return [
            'replies'            => $replies,
            'dvrs'               => $dvrs,
            'complaint_samples'  => $complaint_samples,
            'sample_submissions' => $sample_submissions,
        ];
    }
}
