<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use App\ComplaintSampleHistory;
use App\Traits\SharedCustomerTrait;
class ComplaintSample extends Model
{
    //

    use SharedCustomerTrait;

    protected $appends = ['sample_document_url','sample_document_two_url','sample_document_three_url','courier_document_url'];
    
    public function histories(){
        return $this->hasMany('App\ComplaintSampleHistory','complaint_sample_id')->with(['userinfo','dealerinfo']);
    }

    public function productinfo(){
        return $this->belongsTo('App\Product','product_id')->select('id','product_name','product_code','hsn_code','keywords');
    }

    public function customer(){
        return $this->belongsto('App\Customer','customer_id','id');
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
    }

    public function user(){
        return $this->belongsto('App\User','user_id','id');
    }

    public function dealer(){
        return $this->belongsto('App\Dealer','dealer_id','id');
    }

    public function feedback(){
        return $this->belongsto('App\Feedback','feedback_id','id')->with('product');
    }

    public function getSampleDocumentUrlAttribute(){
        $sample_document_url ="";
        if(!empty($this->sample_document)){
            $sample_document_url = url('ComplaintSampleDocuments/'.$this->sample_document);
        }
        return $sample_document_url;
    }

    public function getSampleDocumentTwoUrlAttribute(){
        $sample_document_url ="";
        if(!empty($this->sample_document_two)){
            $sample_document_url = url('ComplaintSampleDocuments/'.$this->sample_document_two);
        }
        return $sample_document_url;
    }

    public function getSampleDocumentThreeUrlAttribute(){
        $sample_document_url ="";
        if(!empty($this->sample_document_three)){
            $sample_document_url = url('ComplaintSampleDocuments/'.$this->sample_document_three);
        }
        return $sample_document_url;
    }

    public function getCourierDocumentUrlAttribute(){
        $courier_document_url ="";
        if(!empty($this->courier_document)){
            $courier_document_url = url('ComplaintSampleDocuments/'.$this->courier_document);
        }
        return $courier_document_url;
    }

    public static function getComplaintSamples($type, $id, $filters = null)
    {
        $samples = ComplaintSample::with([
            'customer',
            'customer_register_request',
            'productinfo',
            'feedback',
            'histories'
        ]);

        // Role-based filtering
        if ($type === 'executive') {
            //coming form trait
            $samples->forUserSharedData($id);
        } elseif ($type === 'dealer') {
            $samples->where('dealer_id', $id);
        }

        // Optional filters
        if (!empty($filters)) {

            if (!empty($filters['customer_id'])) {
                $samples->where('customer_id', $filters['customer_id']);
            }

            if (!empty($filters['customer_register_request_id'])) {
                $samples->where(
                    'customer_register_request_id',
                    $filters['customer_register_request_id']
                );
            }
        }

        return $samples->get();
    }



    public static function createComplaintSample($request){
        $data = $request->all();
        if(isset($data['complaint_sample_id']) && !empty($data['complaint_sample_id'])){
            $complaint_sample = ComplaintSample::find($data['complaint_sample_id']);
        }else{
            $complaint_sample = new ComplaintSample;
        }
        $getLastRef = ComplaintSample::orderby('id','DESC')->first();
        $getLastRef = json_decode(json_encode($getLastRef),true);
        if(!empty($getLastRef)){
            $number = $getLastRef['id'] + 1;
        }else{
            $number = 1;
        }
        $number = sprintf('%04d',$number);
        $complaint_sample->complaint_sample_no   = "CSN".$number;
        $complaint_sample->request_date = (isset($data['request_date'])?$data['request_date']:'');
        if(isset($data['customer_id']) && !empty($data['customer_id'])){
            $complaint_sample->customer_id = $data['customer_id'];
        }
        if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
            $complaint_sample->customer_register_request_id = $data['customer_register_request_id'];
        }
        $complaint_sample->feedback_id = ((isset($data['feedback_id']) && !empty($data['feedback_id']))?$data['feedback_id']:NULL);
        $complaint_sample->type = (isset($data['type'])?$data['type']:'');
        $complaint_sample->product_id = (isset($data['product_id'])?$data['product_id']:'');
        $complaint_sample->complaint_details_by_customer = (isset($data['complaint_details_by_customer'])?$data['complaint_details_by_customer']:'');
        $complaint_sample->complaint_details_by_you = (isset($data['complaint_details_by_you'])?$data['complaint_details_by_you']:'');
        $complaint_sample->sample_batch_number = (isset($data['sample_batch_number'])?$data['sample_batch_number']:'');
        $complaint_sample->previous_batch_number = (isset($data['previous_batch_number'])?$data['previous_batch_number']:'');
        $complaint_sample->monthly_consumption = (isset($data['monthly_consumption'])?$data['monthly_consumption']:'');
        $complaint_sample->remarks = (isset($data['remarks'])?$data['remarks']:'');
        $complaint_sample->user_id = (isset($data['user_id'])?$data['user_id']:NULL);
        $complaint_sample->dealer_id = (isset($data['dealer_id'])?$data['dealer_id']:NULL);
        if($request->hasFile('sample_document')){
            if (Input::file('sample_document')->isValid()) {
                $file = Input::file('sample_document');
                $destination = 'ComplaintSampleDocuments/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "sample_document".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $complaint_sample->sample_document = $mainFilename;
            }
        }
        if($request->hasFile('sample_document_two')){
            if (Input::file('sample_document_two')->isValid()) {
                $file = Input::file('sample_document_two');
                $destination = 'ComplaintSampleDocuments/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "sample_document_two".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $complaint_sample->sample_document_two = $mainFilename;
            }
        }
        if($request->hasFile('sample_document_three')){
            if (Input::file('sample_document_three')->isValid()) {
                $file = Input::file('sample_document_three');
                $destination = 'ComplaintSampleDocuments/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "sample_document_three".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $complaint_sample->sample_document_three = $mainFilename;
            }
        }
        $histories = array('Sample Collected');
        $complaint_sample->status = "Sample Collected";
        if($request->hasFile('courier_document')){
            $file = $request->file('courier_document');
            $destination = 'ComplaintSampleDocuments/';
            $ext= $file->getClientOriginalExtension();
            $mainFilename = "courier_document".uniqid().date('h-i-s').".".$ext;
            $file->move($destination, $mainFilename);
            $complaint_sample->courier_document = $mainFilename;
            $complaint_sample->status = "Sample Sent";
            $histories = array('Sample Collected','Sample Sent');
        }
        $complaint_sample->save();
        if(isset($data['complaint_sample_id']) && !empty($data['complaint_sample_id'])){

        }else{
            foreach($histories as $history){
                $sample_history = new ComplaintSampleHistory;
                $sample_history->complaint_sample_id = $complaint_sample->id;
                $sample_history->status = $history;
                $sample_history->user_id = (isset($data['user_id'])?$data['user_id']:NULL);
                $sample_history->dealer_id = (isset($data['dealer_id'])?$data['dealer_id']:NULL);
                $sample_history->save();
            }
        }
    }

    public static function editComplaintSample($request){
        $data = $request->all();
        $complaint_sample = ComplaintSample::find($data['complaint_sample_id']);
        if($request->hasFile('sample_document')){
            if (Input::file('sample_document')->isValid()) {
                $file = Input::file('sample_document');
                $destination = 'ComplaintSampleDocuments/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "sample_document".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $complaint_sample->sample_document = $mainFilename;
            }
        }
        $histories = array('Sample Sent');
        if($request->hasFile('courier_document')){
            $file = $request->file('courier_document');
            $destination = 'ComplaintSampleDocuments/';
            $ext= $file->getClientOriginalExtension();
            $mainFilename = "courier_document".uniqid().date('h-i-s').".".$ext;
            $file->move($destination, $mainFilename);
            $complaint_sample->courier_document = $mainFilename;
            $complaint_sample->status = "Sample Sent";
        }
        $complaint_sample->save();
        foreach($histories as $history){
            $sample_history = new ComplaintSampleHistory;
            $sample_history->complaint_sample_id = $complaint_sample->id;
            $sample_history->status = $history;
            $sample_history->user_id = (isset($complaint_sample->user_id)?$complaint_sample->user_id:NULL);
            $sample_history->dealer_id = (isset($complaint_sample->dealer_id)?$complaint_sample->dealer_id:NULL);
            $sample_history->save();
        }
    }
}
