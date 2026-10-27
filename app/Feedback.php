<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SharedCustomerTrait;
class Feedback extends Model
{
    //
    use SharedCustomerTrait;
    
    public function dealer(){
    	return $this->belongsto('App\Dealer','dealer_id','id');
    }
    public function customer(){
    	return $this->belongsto('App\Customer','customer_id','id');
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
    }

    public function customer_employee(){
        return $this->belongsto('App\CustomerEmployee','customer_employee_id','id');
    }

    /** Product columns returned to the app for a feedback. */
    const API_PRODUCT_COLUMNS = ['products.id', 'products.product_name', 'products.version', 'products.discontinued', 'products.not_available', 'products.status'];

    public function product(){
    	return $this->belongsto('App\Product','product_id','id');
    }

    /** Products attached to this feedback (feedback_products table). */
    public function products(){
        return $this->belongsToMany('App\Product','feedback_products','feedback_id','product_id')
            ->withPivot('batch_no')
            ->withTimestamps()
            ->orderBy('feedback_products.id');
    }

    /**
     * Products to attach to a feedback, read from a save-feedback request.
     *
     * - New app builds send `products`: [{product_id, batch_no}, ...]
     *   (a real array, or a JSON string of one when posting form-data).
     * - Old app builds send a single `product_id` + `batch_no`.
     * `products` wins when both are sent. Non-sample feedback only carries
     * products when is_product_related is 1, same as before.
     *
     * @return array [['product_id' => int, 'batch_no' => string|null], ...]
     */
    public static function productsFromRequest(array $data, $isProductRelated){
        if(($data['type'] ?? null) != 'need sample/trial' && $isProductRelated != 1){
            return [];
        }

        if(!empty($data['products']) && is_array($data['products'])){
            $items = $data['products'];
        }elseif(!empty($data['product_id'])){
            $items = [['product_id' => $data['product_id'], 'batch_no' => $data['batch_no'] ?? null]];
        }else{
            return [];
        }

        $products = [];
        foreach($items as $item){
            if(empty($item['product_id'])){
                continue;
            }
            $batchNo = trim((string) ($item['batch_no'] ?? ''));
            $row = ['product_id' => (int) $item['product_id'], 'batch_no' => $batchNo === '' ? null : $batchNo];
            $products[$row['product_id'].'|'.$row['batch_no']] = $row; // drop exact duplicates
        }
        return array_values($products);
    }

    /**
     * Store products in feedback_products. Ids that don't exist in products
     * are dropped instead of failing the save, so old app payloads that used
     * to save keep saving.
     */
    public function saveProducts(array $products){
        $validIds = \App\Product::whereIn('id', array_column($products, 'product_id'))->pluck('id')->flip();

        foreach($products as $row){
            if(isset($validIds[$row['product_id']])){
                $this->products()->attach($row['product_id'], ['batch_no' => $row['batch_no']]);
            }
        }
    }

    /**
     * Shape products for the app: `products` holds every linked product, and
     * `product` holds the first one so old app builds that read a single
     * product keep working. Falls back to the old product_id column when the
     * feedback has no rows in feedback_products yet.
     *
     * Expects `products` and `product` to be eager loaded with API_PRODUCT_COLUMNS.
     */
    public function shapeProductsForApi(){
        $products = $this->products->map(function ($p) {
            $p->setAttribute('batch_no', $p->pivot->batch_no);
            return $p->setAppends(['product_gst'])->makeHidden('pivot');
        });

        $first = $products->first();
        if(!$first && $this->product){
            $first = $this->product->setAppends(['product_gst']);
            $first->setAttribute('batch_no', $this->batch_no);
        }

        $this->setRelation('products', $products);
        $this->setRelation('product', $first);
        $this->makeHidden('product_id');

        return $this;
    }

    public function replies(){
        return $this->hasMany('App\FeedbackReply')->orderby('id','DESC')->with('reply_by');
    }

    public static function feedbackHistories($data){
        $feedbackId = $data['feedback_id'];

        $replies = \App\FeedbackReply::with('reply_by')->where('feedback_id',$feedbackId)->get();

        $dvrs = \App\Dvr::with(['customer','products','customer_register_request','complaint_info','query_info','other_team_member_info','trial_report_info','complaint_sample','market_sample','sample_submission','user_scheduler','trial_reports','user'])->where('complaint_id',$feedbackId)->get();

        /*$dvrs = \App\UserDvr::with([
            'customer',
            'customer_register_request',
            'products',
            'trials.products',
            'trials.attachments',
            'trials.complaint_info',
            'trials.other_team_member_info',
            'customerContacts',
            'attachments',
            'complaint_sample',
            'market_sample',
            'sample_submission',
            'user_scheduler',
            'customer_contact_info',
            'query_info'
        ])->where('complaint_id',$feedbackId)->get();*/

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
