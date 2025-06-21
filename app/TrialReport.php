<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TrialReport;
use App\DvrTrialReport;
use App\TrialReportBath;
use App\TrialReportBathProduct;
use App\Dvr;
class TrialReport extends Model
{
    //
    public function customer(){
        return $this->belongsto('App\Customer','customer_id','id');
    }

    public function dvr_info(){
        return $this->belongsto('App\Dvr','dvr_id','id')->with(['customer','products','customer_register_request','complaint_info','query_info','other_team_member_info']);
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
    }

    public function feedback_info(){
        return $this->belongsto('App\Feedback','feedback_id','id')->with(['customer','customer_employee','product','replies']);
    }

    public function baths(){
        return $this->hasMany('App\TrialReportBath','trial_report_id','id')->with('products');
    }

    public function other_team_member_info(){
        return $this->belongsto('App\User','other_team_member_id','id');
    }


    public static function createOrUpdate($request,$userid){
        $data = $request->all();
        //echo "<pre>"; print_r($data); die;
        if(isset($data['trial_report_id']) && !empty($data['trial_report_id'])){
            $trialReport = TrialReport::find($data['trial_report_id']);
        }else{
            $trialReport = new TrialReport;
        }
        $trialReport->user_id = $userid;
        $trialReport->trial_report_date = $data['trial_report_date'];
        if(isset($data['gsm_glm']) && !empty($data['gsm_glm'])){
            $trialReport->gsm_glm = $data['gsm_glm'];
        }
        if(isset($data['customer_id']) && !empty($data['customer_id'])){
            $trialReport->customer_id = $data['customer_id'];
        }
        if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
            $trialReport->customer_register_request_id = $data['customer_register_request_id'];
        }
        if(isset($data['dvr_id']) && !empty($data['dvr_id'])){
            $trialReport->dvr_id = $data['dvr_id'];
        }
        if(isset($data['feedback_id']) && !empty($data['feedback_id'])){
            $trialReport->feedback_id = $data['feedback_id'];
        }
        if(isset($data['trial_type']) && !empty($data['trial_type'])){
            $trialReport->trial_type = $data['trial_type'];
        }
        if(isset($data['trial_objective']) && !empty($data['trial_objective'])){
            $trialReport->trial_objective = $data['trial_objective'];
        }
        if(isset($data['is_jointly']) && !empty($data['is_jointly'])){
            $trialReport->is_jointly = $data['is_jointly'];
        }
        if(isset($data['other_team_member_id']) && !empty($data['other_team_member_id'])){
            $trialReport->other_team_member_id = $data['other_team_member_id'];
        }
        if(isset($data['other_team_member_name']) && !empty($data['other_team_member_name'])){
            $trialReport->other_team_member_name = $data['other_team_member_name'];
        }
        if(isset($data['substrate_count']) && !empty($data['substrate_count'])){
            $trialReport->substrate_count = $data['substrate_count'];
        }
        if(isset($data['lot_no']) && !empty($data['lot_no'])){
            $trialReport->lot_no = $data['lot_no'];
        }
        if(isset($data['lot_size']) && !empty($data['lot_size'])){
            $trialReport->lot_size = $data['lot_size'];
        }
        if(isset($data['shade']) && !empty($data['shade'])){
            $trialReport->shade = $data['shade'];
        }
        if(isset($data['process_type']) && !empty($data['process_type'])){
            $trialReport->process_type = $data['process_type'];
        }
        if(isset($data['machine_type']) && !empty($data['machine_type'])){
            $trialReport->machine_type = $data['machine_type'];
        }
        if(isset($data['machine_no']) && !empty($data['machine_no'])){
            $trialReport->machine_no = $data['machine_no'];
        }
        if(isset($data['machine_make']) && !empty($data['machine_make'])){
            $trialReport->machine_make = $data['machine_make'];
        }
        if(isset($data['fabric_pick_up']) && !empty($data['fabric_pick_up'])){
            $trialReport->fabric_pick_up = $data['fabric_pick_up'];
        }
        if(isset($data['trough_loss']) && !empty($data['trough_loss'])){
            $trialReport->trough_loss = $data['trough_loss'];
        }
        if(isset($data['solution_required_in_trough']) && !empty($data['solution_required_in_trough'])){
            $trialReport->solution_required_in_trough = $data['solution_required_in_trough'];
        }
        if(isset($data['operator_name']) && !empty($data['operator_name'])){
            $trialReport->operator_name = $data['operator_name'];
        }
        if(isset($data['initial_precautions']) && !empty($data['initial_precautions'])){
            $trialReport->initial_precautions = $data['initial_precautions'];
        }
        if(isset($data['knit_type']) && !empty($data['knit_type'])){
            $trialReport->knit_type = $data['knit_type'];
        }
        if(isset($data['substrate_type']) && !empty($data['substrate_type'])){
            $trialReport->substrate_type = $data['substrate_type'];
        }
        if(isset($data['blend_ratio']) && !empty($data['blend_ratio'])){
            $trialReport->blend_ratio = $data['blend_ratio'];
        }
        if(isset($data['yarn_count']) && !empty($data['yarn_count'])){
            $trialReport->yarn_count = $data['yarn_count'];
        }
        if(isset($data['mangle_pressure']) && !empty($data['mangle_pressure'])){
            $trialReport->mangle_pressure = $data['mangle_pressure'];
        }
        if(isset($data['temp_speed']) && !empty($data['temp_speed'])){
            $trialReport->temp_speed = $data['temp_speed'];
        }
        if(isset($data['process_name']) && !empty($data['process_name'])){
            $trialReport->process_name = $data['process_name'];
        }
        $trialReport->save();
        if(isset($data['dvr_id']) && !empty($data['dvr_id'])){
            Dvr::where('id',$data['dvr_id'])->update(['trial_report_id'=>$trialReport->id]);
            $linkDvrTrialReport = new DvrTrialReport;
            $linkDvrTrialReport->dvr_id = $data['dvr_id'];
            $linkDvrTrialReport->trial_report_id = $trialReport->id;
            $linkDvrTrialReport->save();
        }
        TrialReportBath::where('trial_report_id',$trialReport->id)->delete();
        if(isset($data['baths'])){
            foreach($data['baths'] as $bath){
                //echo "<pre>"; print_r($bath); die;
                $trialbath = new TrialReportBath;
                $trialbath->trial_report_id = $trialReport->id;
                $trialbath->description = $bath['description'];
                $trialbath->material = $bath['material'];
                $trialbath->liquor = $bath['liquor'];
                $trialbath->application_details = $bath['application_details'];
                $trialbath->save();
                if(isset($bath['products'])){
                    foreach($bath['products'] as $product){
                        $bathproduct = new TrialReportBathProduct;
                        $bathproduct->trial_report_bath_id = $trialbath->id;
                        $bathproduct->product_id = $product['product_id'];
                        $bathproduct->product_name = $product['product_name'];
                        $bathproduct->product_description = $product['product_description'];
                        $bathproduct->make = $product['make'];
                        $bathproduct->dosage_type = $product['dosage_type'];
                        $bathproduct->dosage = $product['dosage'];
                        $bathproduct->dosage_kg = $product['dosage_kg'];
                        $bathproduct->gross_price = $product['gross_price'];
                        $bathproduct->discount = $product['discount'];
                        $bathproduct->net_price = $product['net_price'];
                        $bathproduct->application_details = $product['application_details'];
                        $bathproduct->save();
                    }
                }
            }
        }
    }
}
