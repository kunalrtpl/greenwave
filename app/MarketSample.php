<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use App\MarketSampleHistory;
use App\MarketSampleProduct;
class MarketSample extends Model
{
    //
    protected $appends = ['sample_document_url','courier_document_url'];

    public function products(){
        return $this->hasMany('App\MarketSampleProduct','market_sample_id')->with('productinfo');
    }

    public function histories(){
        return $this->hasMany('App\MarketSampleHistory','market_sample_id')->with(['userinfo','dealerinfo']);
    }

    public function customer(){
        return $this->belongsto('App\Customer','customer_id','id');
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
    }

    public function getSampleDocumentUrlAttribute(){
        $sample_document_url ="";
        if(!empty($this->sample_document)){
            $sample_document_url = url('MarketSampleDocuments/'.$this->sample_document);
        }
        return $sample_document_url;
    }

    public function getCourierDocumentUrlAttribute(){
        $courier_document_url ="";
        if(!empty($this->courier_document)){
            $courier_document_url = url('MarketSampleDocuments/'.$this->courier_document);
        }
        return $courier_document_url;
    }

    public static function getMarketSamples($type,$id){
        $samples = MarketSample::with(['customer','customer_register_request','products','histories']);
        if($type == "executive"){
            $samples = $samples->where('user_id',$id);
        }else if($type == "dealer"){
            $samples = $samples->where('dealer_id',$id);
        }
        $samples = $samples->get();
        //echo "<pre>"; print_r(json_decode(json_encode($samples),true));
        return $samples;
    }


    public static function createMarketSample($request){
        $data = $request->all();
        if(isset($data['market_sample_id']) && !empty($data['market_sample_id'])){
            $market_sample = MarketSample::find($data['market_sample_id']);
        }else{
            $market_sample = new MarketSample;
        }
        $getLastRef = MarketSample::orderby('id','DESC')->first();
        $getLastRef = json_decode(json_encode($getLastRef),true);
        if(!empty($getLastRef)){
            $number = $getLastRef['id'] + 1;
        }else{
            $number = 1;
        }
        $number = sprintf('%04d',$number);
        $market_sample->market_sample_no   = "MSN".$number;
        $market_sample->request_date = (isset($data['request_date'])?$data['request_date']:'');
        $market_sample->customer_id = ((isset($data['customer_id']) && !empty($data['customer_id']))?$data['customer_id']:NULL);
        $market_sample->customer_register_request_id = ((isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id']))?$data['customer_register_request_id']:NULL);
        $market_sample->type = (isset($data['type'])?$data['type']:'');
        $market_sample->product_category = (isset($data['product_category'])?$data['product_category']:'');
        $market_sample->product_name = (isset($data['product_name'])?$data['product_name']:'');
        $market_sample->make = (isset($data['make'])?$data['make']:'');
        $market_sample->supplier = (isset($data['supplier'])?$data['supplier']:'');
        $market_sample->price = (isset($data['price'])?$data['price']:'');
        $market_sample->dosage = (isset($data['dosage'])?$data['dosage']:'');
        $market_sample->monthly_consumption = (isset($data['monthly_consumption'])?$data['monthly_consumption']:'');
        $market_sample->product_application = (isset($data['product_application'])?$data['product_application']:'');
        $market_sample->purpose_of_sampling = (isset($data['purpose_of_sampling'])?$data['purpose_of_sampling']:'');
        $market_sample->remarks = (isset($data['remarks'])?$data['remarks']:'');
        $market_sample->is_urgent = (isset($data['is_urgent'])?$data['is_urgent']:'');
        $market_sample->user_id = (isset($data['user_id'])?$data['user_id']:NULL);
        $market_sample->dealer_id = (isset($data['dealer_id'])?$data['dealer_id']:NULL);
        if($request->hasFile('sample_document')){
            if (Input::file('sample_document')->isValid()) {
                $file = Input::file('sample_document');
                $destination = 'MarketSampleDocuments/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "sample_document".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $market_sample->sample_document = $mainFilename;
            }
        }
        $histories = array('Sample Collected');
        $market_sample->status = "Sample Collected";
        if($request->hasFile('courier_document')){
            $file = $request->file('courier_document');
            $destination = 'MarketSampleDocuments/';
            $ext= $file->getClientOriginalExtension();
            $mainFilename = "courier_document".uniqid().date('h-i-s').".".$ext;
            $file->move($destination, $mainFilename);
            $market_sample->courier_document = $mainFilename;
            $market_sample->status = "Sample Sent";
            $histories = array('Sample Collected','Sample Sent');
        }
        $market_sample->save();
        if(isset($data['products']) && !empty($data['products'])){
            $products = explode(',',$data['products']);
            foreach($products as $product){
                $market_product = new MarketSampleProduct;
                $market_product->market_sample_id = $market_sample->id;
                $market_product->product_id = $product;
                $market_product->save();
            }
        }
        foreach($histories as $history){
            $sample_history = new MarketSampleHistory;
            $sample_history->market_sample_id = $market_sample->id;
            $sample_history->status = $history;
            $sample_history->user_id = (isset($data['user_id'])?$data['user_id']:NULL);
            $sample_history->dealer_id = (isset($data['dealer_id'])?$data['dealer_id']:NULL);
            $sample_history->save();
        }
    }

    public static function editMarketSample($request){
        $data = $request->all();
        $market_sample = MarketSample::find($data['market_sample_id']);
        if($request->hasFile('sample_document')){
            if (Input::file('sample_document')->isValid()) {
                $file = Input::file('sample_document');
                $destination = 'MarketSampleDocuments/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "sample_document".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $market_sample->sample_document = $mainFilename;
            }
        }
        $histories = array('Sample Sent');
        if($request->hasFile('courier_document')){
            $file = $request->file('courier_document');
            $destination = 'MarketSampleDocuments/';
            $ext= $file->getClientOriginalExtension();
            $mainFilename = "courier_document".uniqid().date('h-i-s').".".$ext;
            $file->move($destination, $mainFilename);
            $market_sample->courier_document = $mainFilename;
            $market_sample->status = "Sample Sent";
        }
        $market_sample->save();
        foreach($histories as $history){
            $sample_history = new MarketSampleHistory;
            $sample_history->market_sample_id = $market_sample->id;
            $sample_history->status = $history;
            $sample_history->user_id = (isset($market_sample->user_id)?$market_sample->user_id:NULL);
            $sample_history->dealer_id = (isset($market_sample->dealer_id)?$market_sample->dealer_id:NULL);
            $sample_history->save();
        }
    }
}
