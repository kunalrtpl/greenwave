<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use DB;
use Session;
use App\RawMaterial;
use App\Product;
use App\ProductDiscount;
use App\PackingType;
use App\ProductRawMaterial;
use App\RawMaterialChecklist;
use App\PackingSize;
use App\ProductChecklist;
use App\ProductStage;
use App\ProductWeightage;
use App\ProductPricing;
use Validator;
class ProductsController extends Controller
{
    //
    public function products(Request $Request){
        Session::put('active','products'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Product::with(['latest_pro_pricing','pro_checklist','weightages','pricings']);
            if(!empty($data['product_name'])){
                $querys = $querys->where('product_name','like','%'.$data['product_name'].'%');
            }
            if(isset($data['product_type']) && $data['product_type'] >=0){
                $querys = $querys->where('is_trader_product',$data['product_type']);
            }
            if(!empty($data['product_code'])){
                $querys = $querys->where('product_code','like','%'.$data['product_code'].'%');
            }
            if(!empty($data['hsn_code'])){
                $querys = $querys->where('hsn_code','like','%'.$data['hsn_code'].'%');
            }
            if(!empty($data['status'])){
                if($data['status'] =="Active"){
                    $querys = $querys->where('status',1);
                }else if($data['status'] =="Inactive"){
                    $querys = $querys->where('status',0);
                }
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('products.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);

            foreach($querys as $product){ 
                $checked='';
                if($product['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $qc_btn = 'green';
                if($product['is_trader_product'] ==0){
                    if(empty($product['technical_literature']) || empty($product['msds']) || empty($product['pro_checklist'])){
                        $qc_btn = 'red';
                    }
                }else{
                    if(empty($product['pro_checklist'])){
                        $qc_btn = 'red';
                    }
                }

                $costing_btn = 'green';
                if($product['is_trader_product'] ==0){
                    if(/*empty($product['formulation_cost']) ||*/ empty($product['packing_cost']) || empty($product['moq']) || empty($product['weightages']) ||  empty($product['pricings'])){
                        $costing_btn = 'red';
                    }
                }else{
                    if(/*empty($product['formulation_cost']) ||*/ empty($product['packing_cost']) || empty($product['dealer_price']) || $product['moq'] =="" ){
                        $costing_btn = 'red';
                    }
                }
                if($product['status'] == 0){
                    $costing_btn = "grey";
                    $qc_btn = "grey";
                }
                $actionValues = '<a title="Edit Product" class="btn btn-xs green margin-top-10" href="'.url('/admin/add-edit-product/'.$product['id']).'">Basic</a>';

                
                $actionValues .= '<a title="Product QC" class="btn btn-xs '.$qc_btn.' margin-top-10" href="'.url('/admin/product-qc/'.$product['id']).'">QC</a>';
                

                $actionValues .= '<a title="Product Costing" class="btn btn-xs '.$costing_btn.' margin-top-10" href="'.url('/admin/product-costing/'.$product['id']).'">Costing</a>';
                
                $num = ++$i;
                $product_types = product_types();
                $pro_type = $product_types[$product['is_trader_product']];
                $dealer_markup = "";$market_price="";$dealer_price = "";
                if($product['is_trader_product'] == 0 ){
                    if(isset($product['latest_pro_pricing']['dealer_price']) && $product['latest_pro_pricing']['dealer_price'] >0){
                        //for greenwave textile products
                        /*$dealer_markup = $product['latest_pro_pricing']['dealer_markup']."%";*/
                        $dealer_price = "Rs. ".$product['latest_pro_pricing']['dealer_price'];
                    }else{
                        if($product['status'] == 1){
                            $dealer_price = "<strong style='color:red;'>Pending</strong>";
                        }else{
                            $dealer_price = "<strong style='color:gray;'>Pending</strong>";
                        }
                    }

                    if(isset($product['latest_pro_pricing']['market_price']) && $product['latest_pro_pricing']['market_price'] >0){
                        $market_price = "Rs. ".$product['latest_pro_pricing']['market_price'];

                    }else{
                        if($product['status'] == 1){
                            $market_price = "<strong style='color:red;'>Pending</strong>";
                        }else{
                            $market_price = "<strong style='color:gray;'>Pending</strong>";
                        }
                    }   
                }else{
                    $market_price = "<strong style='color:green;'>N.A.</strong>";
                    if(isset($product['dealer_price']) && $product['dealer_price'] >0){
                        //for greenwave textile products
                        /*$dealer_markup = $product['latest_pro_pricing']['dealer_markup']."%";*/
                        $dealer_price = "Rs. ".$product['dealer_price'];
                    }else{
                        if($product['status'] == 1){
                            $dealer_price = "<strong style='color:red;'>Pending</strong>";
                        }else{
                            $dealer_price = "<strong style='color:gray;'>Pending</strong>";
                        }
                    }
                }
                $records["data"][] = array(      
                    '<div style="text-align:center;">'.$num.'</div>',
                    $pro_type,
                    $product['product_name'],
                    '<div style="text-align:right;">'.$dealer_price.'</div>',
                    '<div style="text-align:right;">'.$market_price.'</div>',
                    '<div  id="'.$product['id'].'" rel="products" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Products";
        return View::make('admin.products.products')->with(compact('title'));
    }

    public function addEditProduct(Request $request,$productid=NULL){
        if(!empty($productid)){
            $productdata = Product::with(['raw_materials','pricings','product_stages'])->where('id',$productid)->first();
            $productdata = json_decode(json_encode($productdata),true);
            //echo "<pre>"; print_r($productdata); die;
            $title ="Edit Product";
            $currentStage = ProductStage::getCurretStage($productdata['id']);
        }else{
            $title ="Add Product";
            $productdata =array();
            $currentStage = 'Sample Trial Stage';
        }
        return view('admin.products.add-edit-product')->with(compact('title','productdata','currentStage'));
    }

    public function getProductInheritLayout(Request $request){
        if($request->ajax()){
            $productdata = $request->all();
            return response()->json([
                'view' => (String)View::make('admin.products.product-inherit-layout')->with(compact('productdata')),
            ]);
        }
    }

    public function saveProduct(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                //echo "<pre>"; print_r($data); die;
                if($data['productid']==""){
                    $type ="add";
                    $productcodeUniq = "unique:products,product_code";
                    $productNameUniq = "unique:products,product_name";
                }else{ 
                    $type ="update";
                    $productcodeUniq = "unique:products,product_code,".$data['productid'];
                    $productNameUniq = "unique:products,product_name,".$data['productid'];
                }
                if($data['is_trader_product'] == "0"){
                    $validator = Validator::make($request->all(), [
                            'is_trader_product' => 'bail|required',
                            'product_code' => 'bail|required|'.$productcodeUniq,
                            'product_name' => 'bail|required|'.$productNameUniq,
                            'product_detail_id' => 'bail|required',
                            'short_description' => 'bail|required',
                            'suggested_dosage' => 'bail|required',
                            'packing_type_id' => 'bail|required',
                            'packing_size_id' => 'bail|required',
                            'standard_fill_size' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                            'shelf_life' => 'bail|required|integer',
                            'product_introduced_on' => 'bail|required|date_format:Y-m-d'
                        ]
                    );
                }else{
                    $validator = Validator::make($request->all(), [
                            'is_trader_product' => 'bail|required',
                            'product_code' => 'bail|required|'.$productcodeUniq,
                            'product_name' => 'bail|required|'.$productNameUniq,
                            'product_detail_id' => 'bail|required',
                            'short_description' => 'bail|required',
                            'packing_type_id' => 'bail|required',
                            'packing_size_id' => 'bail|required',
                            'standard_fill_size' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                            'shelf_life' => 'bail|required|integer',
                        ]
                    );
                }
                if($validator->passes()) {
                    $data = $request->all();
                    $totalPercentage  = array_sum($data['percentage']);
                    if($totalPercentage ==100){
                        $count = array_has_dupes($data['raw_material_ids']);
                        if($count > 0){
                            return response()->json(['status'=>false,'errors'=>array('raw_materials'=>'Duplicate Raw materials found')]);
                        }
                        $rmCost = ClacRMcost($data);
                        $data['rm_cost'] = $rmCost;
                    }else{
                        return response()->json(['status'=>false,'errors'=>array('raw_materials'=>'Raw Material Percentage must be 100%')]);

                    }
                    DB::beginTransaction();
                    if($type =="add"){
                        $product = new Product; 
                    }else{
                        $product = Product::find($data['productid']); 
                    }
                   $product->lab_recipe_number = $data['lab_recipe_number'];
                    $product->product_name = $data['product_name'];
                    $product->product_code = $data['product_code'];
                    $product->physical_form = $data['physical_form'];
                    /*$product->show_class = $data['physical_form'];
                    $product->show_weightage = $data['show_weightage'];*/
                    //$product->old_product_code = $data['old_product_code'];
                    $product->product_detail_id = $data['product_detail_id'];
                    $product->product_detail_info = getProductDetailLevel($data['product_detail_id']);
                    $product->packing_type_id = $data['packing_type_id'];
                    $product->additional_packing_type_id = $data['additional_packing_type_id'];
                    $product->standard_fill_size = $data['standard_fill_size'];
                    $product->packing_size_id = $data['packing_size_id'];
                    $product->short_description = $data['short_description'];
                    $product->description = $data['description'];
                    $packingCost = productPackingCost($data);
                    $product->packing_cost = $packingCost;
                    //$product->how_to_use = $data['how_to_use'];
                    $product->suggested_dosage = $data['suggested_dosage'];
                    //$product->hsn_code = $data['hsn_code'];
                    
                    if(isset($data['is_trader_product'])){
                        $product->is_trader_product = $data['is_trader_product'];
                    }else{
                        $product->is_trader_product = 0;
                    }
                    if(isset($data['product_price'])){
                        $product->product_price = $data['product_price'];
                    }else{
                        $product->product_price = 0;
                    }
                    if(isset($data['outsource_packing_cost'])){
                        $product->packing_cost = $data['outsource_packing_cost'];
                    }
                    $product->keywords = $data['keywords'];
                    $product->remarks = $data['remarks'];
                    $product->product_introduced_on = $data['product_introduced_on'];
                    
                    $product->inherit_type = $data['inherit_type'];
                    $product->status = $data['status'];
                    //echo "<pre>"; print_r($data); die;
                    if(isset($data['pro_stage'])){
                        $stages = array_reverse($data['pro_stage']);
                        $product->stage = $stages[0];
                    }
                    if($data['inherit_type'] =="Inhouse"){
                        //$product->batch_out_duration = $data['batch_out_duration'];
                        $product->rm_cost = $data['rm_cost'];
                        /*$product->formulation_cost = $data['formulation_cost'];
                        $product->packing_cost = $data['packing_cost'];*/
                    }
                    /*$product->total_product_cost = $data['product_cost'];
                    $product->dp_calculation_cost = $data['dp_calculation_cost'];
                    $product->company_mark_up = $data['company_mark_up'];
                    $product->dealer_price = $data['dealer_price'];
                    $product->dealer_markup = $data['dealer_markup'];
                    $product->market_price = $data['market_price'];
                    $product->freight = 5;*/
                    $product->shelf_life = $data['shelf_life'];
                    
                    if($request->hasFile('gots_certification')){
                        if (Input::file('gots_certification')->isValid()) {
                            $file = Input::file('gots_certification');
                            $destination = 'images/ProductDocuments/';
                            $ext= $file->getClientOriginalExtension();
                            $mainFilename = "gots_certification".uniqid().date('h-i-s').".".$ext;
                            $file->move($destination, $mainFilename);
                            $product->gots_certification = $mainFilename;
                        }
                    }
                    if($request->hasFile('zdhc_certification')){
                        if (Input::file('zdhc_certification')->isValid()) {
                            $file = Input::file('zdhc_certification');
                            $destination = 'images/ProductDocuments/';
                            $ext= $file->getClientOriginalExtension();
                            $mainFilename = "zdhc_certification".uniqid().date('h-i-s').".".$ext;
                            $file->move($destination, $mainFilename);
                            $product->zdhc_certification = $mainFilename;
                        }
                    }
                    /*if(isset($data['opening_stock'])){
                        $product->opening_stock = $data['opening_stock'];
                        $product->current_stock = $data['opening_stock'];
                    }*/

                    $product->save();
                    ProductRawMaterial::where('product_id',$product->id)->delete();
                    if($data['inherit_type'] =="Inhouse"){
                        foreach($data['raw_material_ids'] as $rkey=> $rawMaterial){
                            $proRawMaterial = new ProductRawMaterial;
                            $proRawMaterial->product_id = $product->id;
                            $proRawMaterial->raw_material_id = $rawMaterial;
                            $proRawMaterial->percentage_included = $data['percentage'][$rkey];
                            $proRawMaterial->save();
                        }
                    }
                    /*if(isset($data['pro_stage'])){
                        foreach ($data['pro_stage'] as $stagekey => $prostage) {
                            $checkStageExists = ProductStage::where('product_id',$product->id)->where('stage',$prostage)->first();
                            if(!$checkStageExists){
                                if(!empty($data['pro_stage_date'][$stagekey])){
                                    $saveProStage = new ProductStage;
                                    $saveProStage->product_id = $product->id;
                                    $saveProStage->stage = $prostage;
                                    $saveProStage->entry_date = $data['pro_stage_date'][$stagekey];
                                    $saveProStage->save();
                                }
                            }
                        }
                    }*/
                    
                    DB::commit();
                    $redirectTo = url('/admin/products?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('name'=>$e->getMessage()."Line No:-".$e->getLine())]);
        }
    }

    public function productQc(Request $request,$productid){
        $qcExtraPermission = \App\UserRole::checkExtraPermission(16,'qc');
        if(!$qcExtraPermission){
            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
        }

        $productdata = Product::with(['raw_materials','pricings','product_stages'])->where('id',$productid)->first();
        $productdata = json_decode(json_encode($productdata),true);
        if($request->isMethod('post')){
            $data = $request->all();
            $product = Product::find($productid);
            if(isset($data['oekotex_certified'])){
                $product->oekotex_certified = $data['oekotex_certified'];
            }else{
                $product->oekotex_certified = 'No';
            }
            if(isset($data['gots_certification'])){
                $product->gots_certification = $data['gots_certification'];
            }else{
                $product->gots_certification = 'No';
            }
            if(isset($data['zdhc_certification'])){
                $product->zdhc_certification = $data['zdhc_certification'];
            }else{
                $product->zdhc_certification = 'No';
            }
            if($request->hasFile('technical_literature')){
                if (Input::file('technical_literature')->isValid()) {
                    $file = Input::file('technical_literature');
                    $destination = 'images/ProductDocuments/';
                    $ext= $file->getClientOriginalExtension();
                    $mainFilename = $request->file('technical_literature')->getClientOriginalName();
                    $file->move($destination, $mainFilename);
                    $product->technical_literature = $mainFilename;
                }
            }
            if($request->hasFile('msds')){
                if (Input::file('msds')->isValid()) {
                    $file = Input::file('msds');
                    $destination = 'images/ProductDocuments/';
                    $ext= $file->getClientOriginalExtension();
                    $mainFilename = $request->file('msds')->getClientOriginalName();
                    $file->move($destination, $mainFilename);
                    $product->msds = $mainFilename;
                }
            }
            $additionalInfo = [];
            if ($request->has('additional_info_labels')) {
                foreach ($request->input('additional_info_labels') as $index => $label) {
                    $value = $request->input('additional_info_values')[$index] ?? '';
                    $additionalInfo[] = [
                        'sr_no' => $index + 1,
                        'label' => $label,
                        'value' => $value,
                    ];
                }
            }

            $product->additional_information = $additionalInfo;
            $product->qc_remarks = $data['qc_remarks']; 
            $product->save();
            ProductChecklist::where('product_id',$productid)->delete();
            if(isset($data['ranges']) && !empty($data['ranges'])){
                foreach($data['ranges'] as $rkey=> $range){
                    if(!empty($range)){
                        $proChecklist = new ProductChecklist;
                        $proChecklist->product_id= $productid;
                        $proChecklist->checklist_id = $data['checklist_ids'][$rkey];
                        $proChecklist->range = $range;
                        $proChecklist->remarks = $data['remarks'][$rkey];
                        $proChecklist->save();
                    }
                }
            }
            return redirect()->back()->with('flash_message_success','Information has been updated successfully');
        }
        $title = "Product QC";
        return view('admin.products.qc')->with(compact('title','productdata','productid'));
    }

    public function productCosting(Request $request, $productid){
        $costingExtraPermission = \App\UserRole::checkExtraPermission(16,'costing');
        if(!$costingExtraPermission){
            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
        }

        $productdata = Product::with(['raw_materials','pricings','product_stages','weightages'])->where('id',$productid)->first();
        $productdata = json_decode(json_encode($productdata),true);
        if($request->isMethod('post')){
            $data = $request->all();
            $data['formulation_cost'] = 0;
            $product = Product::find($productid);
            $product->formulation_cost = $data['formulation_cost'];
            //$product->packing_cost = $data['packing_cost'];
            $product->average_dispatch_time = $data['average_dispatch_time'];
            if($product->formulation_cost >0 && $product->packing_cost >0){
                $product->total_product_cost = $product->rm_cost + $product->formulation_cost + $product->packing_cost ;
            }
            $product->dealer_price = $data['dealer_price'];
            if($product->is_trader_product == 0){
                $product->dp_calculation_cost = $data['dealer_price'];
                $product->company_mark_up     = 0;
                $product->freight = 6;
                $product->landed_price = $data['landed_price'];
                $product->dealer_markup = $data['dealer_markup'];
                $product->market_price = $data['market_price'];
                //$product->free_sample_unit = $data['free_sample_unit'];
            }
            $product->moq = $data['moq'];
            $product->save();
            //echo "<pre>"; print_r($data); die;
            if(isset($data['weightages']) && !empty($data['weightages'])){
                foreach($data['weightages'] as $wkey => $weightage){
                    if(isset($data['weightage_ids'][$wkey])){
                        $proweight = ProductWeightage::find($data['weightage_ids'][$wkey]);
                    }else{
                        $proweight = new ProductWeightage;
                        $proweight->product_id = $productid;
                        $proweight->month = $data['weightage_months'][$wkey];
                        $proweight->year = $data['weightage_years'][$wkey];
                        $proweight->start_date = $data['weightage_years'][$wkey].'-'.$data['weightage_months'][$wkey].'-01';
                        $proweight->created_by = \Auth::user()->id;
                    }
                    $proweight->weightage = $weightage;
                    $proweight->save();
                }
            }
            if(isset($data['is_delete_weightages'])){
                ProductWeightage::wherein('id',$data['is_delete_weightages'])->delete();
            }
            if(isset($data['mp_prices'])){
                //Product Pricings
                foreach ($data['mp_prices'] as $mpkey => $marketPriceInfo) {
                    if(!empty($marketPriceInfo) || !empty($data['dp_prices'][$mpkey])){
                        $proPricing = new ProductPricing;
                        $proPricing->product_id = $product->id;
                        $proPricing->market_price = $marketPriceInfo;
                        $proPricing->dealer_price = $data['dp_prices'][$mpkey];
                        if(!empty($marketPriceInfo) && !empty($data['dp_prices'][$mpkey])){
                            $dealer_markup = $marketPriceInfo - ($data['dp_prices'][$mpkey] +6);
                            $dealer_markup = round(($dealer_markup * 100)/$marketPriceInfo,2);
                            $proPricing->dealer_markup = $dealer_markup;
                        }
                        $proPricing->price_date = $data['dates'][$mpkey];
                        $proPricing->save();
                    }
                }
            }
            if(isset($data['is_delete'])){
                ProductPricing::wherein('id',$data['is_delete'])->delete();
            }
            return redirect()->back()->with('flash_message_success','Information has been updated successfully');
        }
        $title = "Product Costing";
        return view('admin.products.product-costing')->with(compact('title','productdata','productid'));
    }

    public function addMoreRawMaterial(Request $request){
        if($request->ajax()){
            return response()->json([
                'view' => (String)View::make('admin.products.product-raw-material'),
            ]);
        }
    }

    public function calculateRMCost(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $totalPercentage  = array_sum($data['percentage']);
            if($totalPercentage ==100){
                $count = array_has_dupes($data['raw_material_ids']);
                if($count > 0){
                    return array('status'=>false,'message'=> 'Duplicate Raw materials found');
                }
                $rmCost = ClacRMcost($data);
                return array('status'=>true,'rm_cost'=> round($rmCost));
            }else{
                return array('status'=>false,'message'=> 'Raw Material Percentage must be 100%');
            }
        }
    }

    public function rawMaterials(Request $Request){
        Session::put('active','rawmaterials'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = RawMaterial::with('latest_raw_material');
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            if(!empty($data['coding'])){
                $querys = $querys->where('coding','like','%'.$data['coding'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('raw_materials.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            //echo "<pre>"; print_r($querys); die;
            foreach($querys as $rawMaterial){ 
                $checked='';
                if($rawMaterial['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit Raw Material" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-raw-material/'.$rawMaterial['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $latest_price = "Rs. ".$rawMaterial['price'];
                if(isset($rawMaterial['latest_raw_material']['price'])){
                    $latest_price = "Rs. ".$rawMaterial['latest_raw_material']['price'];
                }
                $records["data"][] = array(      
                    '<div style="text-align:center;">'.$num.'</div>',
                    $rawMaterial['name'],
                    '<div style="text-align:right;">'."Rs. ".$rawMaterial['price'].'</div>',
                    '<div  id="'.$rawMaterial['id'].'" rel="raw_materials" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Raw Materials";
        return View::make('admin.products.raw-materials')->with(compact('title'));
    }

    public function addEditRawMaterial(Request $request,$rawmaterialid=NULL){
    	if(!empty($rawmaterialid)){
    		$rawmaterialdata = RawMaterial::where('id',$rawmaterialid)->first();
    		$title ="Edit Raw Material";
    	}else{
    		$title ="Add Raw Material";
	    	$rawmaterialdata =array();
    	}
    	return view('admin.products.add-edit-raw-material')->with(compact('title','rawmaterialdata'));
    }

    public function saveRawMaterial(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['rawmaterialid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'name' => 'bail|required',
                        //'coding' => 'bail|required',
                        'price' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gte:0',
                        'shelf_life' => 'bail|required|integer',
                        /*'opening_stock' => 'bail|required|integer',*/
                        'status' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $rawmaterial = new RawMaterial; 
                    }else{
                        $rawmaterial = RawMaterial::find($data['rawmaterialid']); 
                    }
                    $rawmaterial->name = $data['name'];
                    $rawmaterial->coding = $data['name'];
                    $rawmaterial->price = $data['price'];
                    $rawmaterial->shelf_life = $data['shelf_life'];
                    /*if(isset($data['opening_stock'])){
                        $rawmaterial->opening_stock = $data['opening_stock'];
                        $rawmaterial->current_stock = $data['opening_stock'];
                    }*/
                    $rawmaterial->status = 1;
                    $rawmaterial->save();
                    //echo "<pre>"; print_r($data); die;
                    if(isset($data['ranges']) && !empty($data['ranges'])){
                        foreach($data['ranges'] as $rkey=> $range){
                            /*if(!empty($range)){*/
                                if(isset($data['rm_checklistds'][$rkey])){
                                    $rmChecklist = RawMaterialChecklist::find($data['rm_checklistds'][$rkey]);
                                }else{
                                    $rmChecklist = new RawMaterialChecklist;
                                }
                                $rmChecklist->raw_material_id= $rawmaterial->id;
                                $rmChecklist->checklist_id = $data['checklist_ids'][$rkey];
                                $rmChecklist->range = $range;
                                $rmChecklist->remarks = $data['remarks'][$rkey];
                                $rmChecklist->save();
                            /*}else{
                                if(isset($data['rm_checklistds'][$rkey])){
                                    RawMaterialChecklist::find($data['rm_checklistds'][$rkey])->delete();
                                }
                            }*/
                        }
                    }
                    $getProids = ProductRawMaterial::where('raw_material_id',$rawmaterial->id)->pluck('product_id')->toArray();
                    foreach($getProids as $proid){
                        $proinfo = Product::with(['raw_materials'=>function($query){
                            $query->with('rawmaterial');
                        }])->where('id',$proid)->first();
                        $proinfo = json_decode(json_encode($proinfo),true);
                        if(!empty($proinfo)){
                            $rmCost = 0;
                            foreach($proinfo['raw_materials'] as $key => $rawMaterial) {
                                $rmCost += (($rawMaterial['rawmaterial']['price'] * $rawMaterial['percentage_included'])/100);
                            }
                            $totalProCost = $rmCost + $proinfo['formulation_cost']  + $proinfo['packing_cost'];
                            $dealerPrice = ($totalProCost / (1- ($proinfo['company_mark_up'] /100)));
                            $marketprice = ($dealerPrice + $proinfo['freight']) / (1- ($proinfo['dealer_markup'] /100)) ;
                            Product::where('id',$proinfo['id'])->update(['rm_cost'=>$rmCost,'total_product_cost'=>$totalProCost,'dp_calculation_cost'=>$totalProCost,'dealer_price'=>round($dealerPrice),'market_price'=>round($marketprice)]);
                        }
                    }
                    $redirectTo = url('/admin/raw-materials?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('name'=>$e->getMessage())]);
        }
    }

    public function packingSizes(Request $Request){
        Session::put('active','packingsizes'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PackingSize::query();
            if(!empty($data['size'])){
                $querys = $querys->where('size','like','%'.$data['size'].'%');
            }
            if(!empty($data['type'])){
                $querys = $querys->where('type','like','%'.$data['type'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('packing_sizes.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $packingSize){ 
                $checked='';
                if($packingSize['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit Packing Size" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-packing-size/'.$packingSize['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a onclick="return ConfirmDelete()"  title="Delete Packing Size" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-packing-size/'.$packingSize['id']).'"> <i class="fa fa-times"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $packingSize['id'],
                    $packingSize['size'],
                    //$packingSize['current_stock'],
                    '<div  id="'.$packingSize['id'].'" rel="packing_sizes" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Order Size";
        return View::make('admin.products.packing-sizes')->with(compact('title'));
    }

    public function addEditPckingSize(Request $request,$packingsizeid=NULL){
    	if(!empty($packingsizeid)){
    		$sizedata = PackingSize::where('id',$packingsizeid)->first();
    		$title ="Edit Order Size";
    	}else{
    		$title ="Add Order Size";
	    	$sizedata =array();
    	}
    	return view('admin.products.add-edit-packing-size')->with(compact('title','sizedata'));
    }

    public function savePackingSize(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['packingsizeid']==""){
                    $type ="add";
                    $size_unique = "unique:packing_sizes,size";
                }else{ 
                    $type ="update";
                    $size_unique = "unique:packing_sizes,size,".$data['packingsizeid'];
                }
                $validator = Validator::make($request->all(), [
                        'size' => 'bail|required|integer|'.$size_unique,
                        'status' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $packingsize = new PackingSize; 
                    }else{
                        $packingsize = PackingSize::find($data['packingsizeid']); 
                    }
                    $packingsize->size = $data['size'];
                    $packingsize->status = $data['status'];
                    $packingsize->save();
                    if($type =="update"){
                        $this->syncProductPackingCost($packingsize,'packingsize');
                    }
                    $redirectTo = url('/admin/packing-sizes?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('size'=>$e->getMessage())]);
        }
    }

    public function deletePackingSize($packingsizeid){
        PackingSize::where('id',$packingsizeid)->delete();
        return redirect::to('/admin/packing-sizes')->with('flash_message_success','Order Size has been deleted successfully');
    }

    public function packingTypes(Request $Request){
        Session::put('active','packingtypes'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PackingType::query();
            if(!empty($data['department'])){
                $querys = $querys->where('department','like','%'.$data['department'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('packing_types.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $packingtype){
                $checked='';
                if($packingtype['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-packing-type/'.$packingtype['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $packingtype['id'],
                    $packingtype['name'],
                    $packingtype['tare_weight'],
                    "Rs. ".$packingtype['price'],
                    '<div  id="'.$packingtype['id'].'" rel="packing_types" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Packing Types";
        return View::make('admin.products.packing-types')->with(compact('title'));
    }

    public function addEditPackingType(Request $request,$packingtypeid=NULL){
        if(!empty($packingtypeid)){
            $packingtypedata = PackingType::where('id',$packingtypeid)->first();
            $title ="Edit Packing Type";
        }else{
            $title ="Add Packing Type";
            $packingtypedata =array();
        }
        return view('admin.products.add-edit-packing-type')->with(compact('title','packingtypedata'));
    }

    public function savePackingType(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['packingtypeid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'name' => 'bail|required',
                        'tare_weight' => 'bail|required|regex:/^\d+(\.\d{1,3})?$/',
                        'price' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/'
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $packingtype = new PackingType; 
                    }else{
                        $packingtype = PackingType::find($data['packingtypeid']); 
                    }
                    $packingtype->name = $data['name'];
                    $packingtype->tare_weight = $data['tare_weight'];
                    $packingtype->price = $data['price'];
                    $packingtype->lab_sample = 0;
                    $packingtype->additional_packing = 0;
                    if(isset($data['lab_sample'])){
                        $packingtype->lab_sample = 1;
                    }
                    if(isset($data['additional_packing'])){
                        $packingtype->additional_packing = 1;
                    }
                    $packingtype->status = 1;
                    $packingtype->save();
                    if($type =="update"){
                        $this->syncProductPackingCost($packingtype,'packingtype');
                    }
                    $redirectTo = url('/admin/packing-types?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('name'=>$e->getMessage()."Line No:-".$e->getLine())]);
        }
    }


    public function syncProductPackingCost($packing,$type){
        if($type=="packingtype"){
            $products = Product::where('packing_type_id',$packing->id)->get()->toArray();
        }else{
            $products = Product::where('packing_size_id',$packing->id)->get()->toArray();
        }
        foreach($products as $product){
            $data = array();
            $data['packing_type_id'] = $product['packing_type_id'];
            $data['additional_packing_type_id'] = $product['additional_packing_type_id'];
            $data['packing_size_id'] = $product['packing_size_id'];
            $data['standard_fill_size'] = $product['standard_fill_size'];
            $packing_cost = productPackingCost($data);
            $info = Product::find($product['id']);
            $info->packing_cost = $packing_cost;
            $info->save();
        }
    }

    public function productDiscounts(Request $Request){
        Session::put('active','productDiscounts'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = ProductDiscount::query();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('product_discounts.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $productdis){ 
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-product-discount/'.$productdis['id']).'"> <i class="fa fa-edit"></i>
                    </a><a title="Delete" class="btn btn-sm red margin-top-10"  href="'.url('/admin/delete-product-discount/'.$productdis['id']).'" > <i class="fa fa-times"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $num,
                    "Rs. " .$productdis['range_from'],
                    "Rs. " .$productdis['range_to'],
                    $productdis['discount']."%", 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "SPSOD";
        return View::make('admin.products.discounts.product-discounts')->with(compact('title'));
    }

    public function addEditProductDiscount(Request $request,$productdiscountid=NULL){
        if(!empty($productdiscountid)){
            $prodiscountdata = ProductDiscount::where('id',$productdiscountid)->first();

            $title ="Edit SPSOD";
        }else{
            $title ="Add SPSOD";
            $prodiscountdata =array();
        }
        return view('admin.products.discounts.add-edit-product-discount')->with(compact('title','prodiscountdata'));
    }

    public function saveProductDiscount(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['productdiscountid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:'.$data['range_from'],
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $proDis = new ProductDiscount; 
                    }else{
                        $proDis = ProductDiscount::find($data['productdiscountid']);
                    }
                    $proDis->product_id = NULL;
                    $proDis->range_from = $data['range_from'];
                    $proDis->range_to   = $data['range_to'];
                    $proDis->discount   = $data['discount'];
                    $proDis->save();
                    $redirectTo = url('/admin/product-discounts?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function deleteProductDiscount($disid){
        ProductDiscount::where('id',$disid)->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function deleteProductDocument($type,$proid){
        Product::where('id',$proid)->update([$type=>'']);
        return redirect()->back();
    }
}
