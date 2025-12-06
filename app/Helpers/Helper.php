<?php
	use App\Region;
	use App\PackingType;
    use App\PackingSize;
	use App\Checklist;

	function qc_checklists(){
    	$checklists = Checklist::with(['subchecklists'=>function($query){
    		$query->with('subchecklists');
    	}])->where(['parent_id'=>NULL,'status'=>1])->orderby('id','ASC')->get();
    	$checklists = json_decode(json_encode($checklists),true);
       // echo "<pre>"; print_r($checklists); die;
    	return $checklists;
    }

	function countries(){
		$countries = DB::table('countries')->where('status',1)->orderby('sort','ASC')->get();
		return $countries;
	}

	function states($countryname){
		$states = DB::table('states')->where('country_name',$countryname)->where('status',1)->orderby('state_name','ASC')->get();
		return $states;
	}

	function cities($statename){
		$cities = DB::table('cities')->where('state_name',$statename)->where('status',1)->orderby('state_name','ASC')->get();
		return $cities;
	}

	function departments(){
		$depts = DB::table('departments')->where('status',1)->orderby('department','ASC')->get();
		$depts = json_decode(json_encode($depts),true);
		return $depts;
	}

	function getDesignationParent($parentid){
		if($parentid=="ROOT"){
			return 'ROOT';
		}else{
			$designationInfo = DB::table('designations')->where('id',$parentid)->first();
			return $designationInfo->designation;
		}
	}

	function getRegionParent($parentid){
		if($parentid=="ROOT"){
			return 'ROOT';
		}else{
			$regionInfo = DB::table('regions')->where('id',$parentid)->first();
			return $regionInfo->region;
		}
	}

	function regions(){
		$regions = Region::with(['subregions'])->where(['parent_id'=>'ROOT'])->get()->toArray();
		return $regions;
	}

	function getStateName($city){
		$stateInfo = DB::table('cities')->where('city_name',$city)->select('state_name')->first();
		if($stateInfo){
			return $stateInfo->state_name;
		}else{
			return '';
		}
	}

	function getcities(){
		$cities = DB::table('cities')->where('status',1)->get();
		$cities = json_decode(json_encode($cities),true);
		return $cities;
	}

	function product_details(){
    	$details = DB::table('product_details')->where('type','child')->where('status',1)->get();
    	$details = json_decode(json_encode($details),true);
    	return $details;
    }

    function rawmaterials(){
    	$details = DB::table('raw_materials')->where('status',1)->get();
    	$details = json_decode(json_encode($details),true);
    	return $details;
    }

    function array_has_dupes($array) {
	   // streamline per @Felix
	   return count($array) !== count(array_unique($array));
	}

	function ClacRMcost($data){
		$rmCost = 0;
	    foreach ($data['raw_material_ids'] as $key => $rawMaterial) {
	        $rawMaterialInfo = DB::table('raw_materials')->where('id',$rawMaterial)->first();
	        $rmCost += (($rawMaterialInfo->price * $data['percentage'][$key])/100);
	    }
	    return $rmCost;
	}

	function products($type=null){
		$products = DB::table('products')->where('products.status',1)->select('products.id','products.product_code','products.product_name','products.packing_size_id','packing_sizes.size')->join('packing_sizes','packing_sizes.id','=','products.packing_size_id');
		/*if(isset($type)){
			$products = $products->where('products.inherit_type','Inhouse');
		}*/
		$products = $products->orderby('products.product_name','ASC')->get();
		$products = json_decode(json_encode($products),true);
		return $products;
	}

    function jobcardProducts(){
        $products = DB::table('products')->where('products.status',1)->select('products.id','products.product_code','products.product_name','products.packing_type_id','packing_types.name as standard_packing_type','products.standard_fill_size','packing_types.stock as packing_available_stock')->join('packing_types','packing_types.id','=','products.packing_type_id');
        $products = $products->orderby('products.product_name','ASC')->get();
        $products = json_decode(json_encode($products),true);
        return $products;
    }

	function ospProducts(){
		$products = DB::table('products')->where('status',1)->select('id','product_code','product_name','is_trader_product','product_price')->get();
		$products = json_decode(json_encode($products),true);
		return $products;
	}

	function activities(){
		/*return array('Garment Dyeing','Package Dyeing','Fabric Washing','Fabric Dyeing','Fabric Printing','CBR','CDR','Towel Unit','Fabric Sale','Yarn Sale','Garment Manufacturing');*/
		return array(
		    'CBR',
		    'CDR',
		    'Fabric Printing (Knits)',
		    'Fabric Printing (Woven)',
		    'Fabric Dyeing & Finishing (Knits)',
		    'Fabric Dyeing & Finishing (Woven)',
		    'Fabric Washing',
		    'Fiber Dyeing',
		    'Garment Washing/ Dyeing',
		    'Hank Dyeing',
		    'Home Furnishing Processing',
		    'Mink Blankets Processing',
		    'Package Dyeing (Thread)',
		    'Package Dyeing (Yarn)',
		    'Towel Unit'
		);
	}

	function buisnesModels(){
		/*return array('Dealer','Direct Company','Dealer + Company','Open');*/
		return array('Dealer','Direct Customer','Open');
	}

	function dealers(){
		$dealers = DB::table('dealers')->select('id','business_name','owner_name')->whereNULL('parent_id')->where('status',1)->get();
		$dealers = json_decode(json_encode($dealers),true);
		return $dealers;
	}

	function productinfo($id){
		$details = DB::table('products')->where('id',$id)->select('id','product_code','product_name')->first();
		$details = json_decode(json_encode($details),true);
		return $details;
	}

	function checklists(){
		$checklists = DB::table('checklists')->where('status',1)->get();
		$checklists = json_decode(json_encode($checklists),true);
		return $checklists;
	}

	function getPackingTypes(){
		$types = PackingType::where('status',1)->pluck('name')->toArray();
		return $types;
	}

	function getProductDetailLevel($id){
		$detailLevel = DB::select("SELECT T2.id, T2.name
					FROM (
					    SELECT
					        @r AS _id,
					        (SELECT @r := parent_id FROM product_details WHERE id = _id) AS parent_id,
					        @l := @l + 1 AS lvl
					    FROM
					        (SELECT @r := ".$id.", @l := 0) vars,
					        product_details h
					    WHERE @r <> 0) T1
					JOIN product_details T2
					ON T1._id = T2.id
					ORDER BY T1.lvl DESC");
		$detailLevels = json_decode(json_encode($detailLevel),true);
		$levels="";
		foreach ($detailLevels as $key => $detailLevel) {
			$levels .= $detailLevel['name']. " -> ";
		}
		$levels = rtrim($levels, ' -> ');
		return $levels;
	}

	function getMaterialTypes(){
		$materialTypes = array('RM'=>'Raw Material','PM'=>'Packing Material','PL'=>'Packing Label','SRM'=> 'Sale Return Material','RFDM'=>'Finished Product (RFD)');
		return $materialTypes;
	}


	function materialStatus(){
		return array('Incoming Material','Sample Sent to Lab','Sample Received by Lab','QC Process Initiated');
	}

	function IHPstatus(){
		return array('Sample Sent to Lab','Sample Received by Lab','QC Process Initiated');
	}

	function OSPstatus(){
		return array('Incoming Material','Sample Sent to Lab','Sample Received by Lab','QC Process Initiated');
	}

	function batchSheetStatus(){
		return array('RM Requested','RM Issued','Sample Sent to Lab','Sample Received by Lab','QC Process Initiated');
	}

	function apiSuccessResponse($message,$data=NULL){
        $success = [
            'status'       => true,
            'code'          => 200,
            'message'       => $message, 
            'data'          => $data
        ];
        return $success;
    }

    function validationResponse($validator){
        foreach($validator->errors()->toArray() as $v => $a){
            $validationError = [
                'status'       => false,
                'code'          => 422,
                'message'       => $a[0],
            ];
            return $validationError;
        }
    }

    function apiErrorResponse($message,$code=422,$redirect=NULL){
        $error = [
            'status'       => false,
            'code'          => $code,
            'redirect_to'     => $redirect,
            'message'       => $message,
        ];

        return $error;
    }

    function getInventoryAccess($type){
    	if($type=="view"){
    		$status = explode(',',Auth::user()->view_inventory_access);
    	}else{
    		$status = explode(',',Auth::user()->update_inventory_access);
    	}
    	return $status;
    }

    function getIHPAccess($type){
    	if($type=="view"){
    		$status = explode(',',Auth::user()->view_ihp_access);
    	}else{
    		$status = explode(',',Auth::user()->update_ihp_access);
    	}
        $status = array_filter($status);
    	return $status;
    }

    function getOSPAccess($type){
    	if($type=="view"){
    		$status = explode(',',Auth::user()->view_osp_access);
    	}else{
    		$status = explode(',',Auth::user()->update_osp_access);
    	}
    	return $status;
    }

    function getRMInventoryUpdateAccess($currentStatus){
    	$nextStatus ="";
    	if($currentStatus =="Incoming Material"){
    		$nextStatus = "Sample Sent to Lab";
    	}else if($currentStatus =="Sample Sent to Lab"){
    		$nextStatus = "Sample Received by Lab";
    	}else if($currentStatus =="Sample Received by Lab"){
    		$nextStatus = "QC Process Initiated";
    	}
    	$userUpdateInvAccess = explode(',',Auth::user()->update_inventory_access);
    	if(in_array($nextStatus,$userUpdateInvAccess)){
    		return true;
    	}else{
    		return false;
    	}
    }

    function getOSPInventoryUpdateAccess($currentStatus){
    	$nextStatus ="";
    	if($currentStatus =="Incoming Material"){
    		$nextStatus = "Sample Sent to Lab";
    	}else if($currentStatus =="Sample Sent to Lab"){
    		$nextStatus = "Sample Received by Lab";
    	}else if($currentStatus =="Sample Received by Lab"){
    		$nextStatus = "QC Process Initiated";
    	}else if($currentStatus =="QC Approved"){
    		$nextStatus = "Packing & Labelling";
    	}
    	$userUpdateInvAccess = explode(',',Auth::user()->update_osp_access);
    	if(in_array($nextStatus,$userUpdateInvAccess)){
    		return true;
    	}else{
    		return false;
    	}
    }

    function getBatchSheetUpdateAccess($currentStatus){
    	$nextStatus ="";
    	if($currentStatus =="RM Requested"){
    		$nextStatus = "RM Issued";
    	}elseif($currentStatus =="RM Issued"){
    		$nextStatus = "Sample Sent to Lab";
    	}else if($currentStatus =="Sample Sent to Lab"){
    		$nextStatus = "Sample Received by Lab";
    	}else if($currentStatus =="Sample Received by Lab"){
    		$nextStatus = "QC Process Initiated";
    	}
    	$userUpdateInvAccess = explode(',',Auth::user()->update_ihp_access);
    	if(in_array($nextStatus,$userUpdateInvAccess)){
    		return true;
    	}else{
    		return false;
    	}
    }

    function financialYear(){
    	if ( date('m') > 3 ) {
            $financial_year = date('Y')."-".(date('y') + 1);
        }
        else {
            $financial_year = (date('Y') -1)."-".date('y');
        }
        return $financial_year;
    }

    function geClass($markup){
    	$class = \App\ProductClass::where('from','<=',$markup)->where('to','>=',$markup)->where('status',1)->first();
    	if(is_object($class)){
    		return $class->class_name;
    	}else{
    		return '';
    	}
    }

    function pro_classes(){
    	$classes = \App\ProductClass::where('status',1)->get();
    	$classes = json_decode(json_encode($classes),true);
    	return $classes;
    }

    function product_types(){
    	return array(
    		'0' =>'Greenwave Textile Products',
    		'2' =>'Greenwave Overseas Products',
    		'3' =>'Greenwave Paper Products',
    		'4' =>'Greenwave Detergent Products',
    		'1' =>'Neutral Products',
    	);
    }

    function physical_forms(){
        return array('Liquid','Powder');
    }

    function classes(){
        return array('Yes','No');
    }

    function productPackingCost($data){

	    // Get main packing type info
	    $packing_info = PackingType::where('id',$data['packing_type_id'])->first();
	    $packing_info = json_decode(json_encode($packing_info), true);
	    if(!empty($packing_info)){
	    	// Base cost = packing price divided by standard fill size
		    $cost = $packing_info['price'] / $data['standard_fill_size'];

		    // Get the order size (Packing size)
		    $order_size = PackingSize::where('id',$data['packing_size_id'])->first();
		    $order_size = json_decode(json_encode($order_size), true);

		    // Initialize cost variables
		    $additional_cost = 0;           // Extra packing material cost
		    $label_cost = 0;                // Cost for label (if any)
		    $additionalPackingCost = 0;     // Flag used for additional label cost logic
		    $facilitation_cost = 0;         // Facilitation cost when additional packing = Yes(1)

		    // If an additional packing type is selected
		    if (!empty($data['additional_packing_type_id'])) {

		        // Get additional packing type info
		        $additional_packing_info = PackingType::where('id',$data['additional_packing_type_id'])->first();
		        $additional_packing_info = json_decode(json_encode($additional_packing_info), true);

		        // Additional packing cost = price / order size
		        $additional_cost = $additional_packing_info['price'] / $order_size['size'];

		        // Used in label calculation
		        $additionalPackingCost = 1;

		        // If this packing type is marked as "additional packing" (1)
		        // add facilitation cost
		        if ($additional_packing_info['additional_packing'] == 1) {
		            $facilitation_cost = $additional_packing_info['facilitation_cost'];
		        }
		    }

		    // Label cost calculation (if label is selected)
		    if (!empty($data['label_id'])) {

		        // Get label info
		        $label = \App\Label::where('id',$data['label_id'])->first();

		        // Base label price from DB
		        $baseCost = $label->price;

		        /*
		            Label cost formula:
		            (label price * (size ratio + additionalPackingCostFlag)) / order_size
		            - sizeRatio = order_size / standard_fill_size
		            - additionalPackingCostFlag = 1 if additional packing is applied
		        */
		        $label_cost = ($baseCost * (($order_size['size'] / $data['standard_fill_size']) + $additionalPackingCost)) / $order_size['size'];
		    }

		    // Final individual costs
		    $basic_packing_material_cost = $cost;
		    $additional_packing_material_cost = $additional_cost;

		    // Total packing cost = base + additional + label + facilitation
		    $packing_cost = $cost + $additional_cost + $label_cost + $facilitation_cost;

		    // Return everything
		    return array(
		        'basic_packing_material_cost'      => $basic_packing_material_cost,
		        'additional_packing_material_cost' => $additional_packing_material_cost,
		        'label_cost'      => $label_cost,
		        'facilitation_cost'  => $facilitation_cost,
		        'packing_cost'       => $packing_cost
		    ); 
	    }else{
	    	return array(
		        'basic_packing_material_cost'      =>0,
		        'additional_packing_material_cost' =>0,
		        'label_cost'      => 0,
		        'facilitation_cost'  => 0,
		        'packing_cost'       => 0
		    );
	    }
	}

    function job_card_types(){
        return array('Standard Recipe'=>'Standard Recipe','Conversion'=>'Conversion (Change of Name or Batch No.)','Dilution'=>'Dilution','Reprocess'=>'Reprocess','Batch Merge'=>'Batch Merge');
    }

    function has_dupes($array) {
        $hasDuplicates = count($array) > count(array_unique($array));
        return $hasDuplicates;
    }

    function getDiffDays($earlier,$later){
    	$earlier = new \DateTime($earlier);
		$later = new \DateTime($later);
		$pos_diff = $earlier->diff($later)->format("%r%a");
		return $pos_diff;
    }

    function app_roles($type){
    	$app_roles = DB::table('app_roles')->where('type',$type)->orderby('sort_order','asc')->get();
        $app_roles = json_decode(json_encode($app_roles),true);
        return $app_roles;
    }

    function getUserRoles($rolekeys,$type){
    	$rolekeys = explode(',',$rolekeys);
        $app_roles = DB::table('app_roles')->select('key','name_app','locked')->wherein('key',$rolekeys)->where('type',$type)->get();
        return $app_roles;
    }

    function formatQty($value){
        // Convert to float and check if it has decimal part
        if (is_numeric($value)) {
            return (strpos($value, '.') !== false && ((float)$value == (int)$value))
                ? (int)$value // Convert to integer if all decimal places are 0
                : $value; // Keep original value
        }

        return $value;
    }

    function sendSms($params){
    	$key = "VCjKFCXQgrrJAMff";	
		$mbl= $params['mobile'];
		$message_content=urlencode($params['message']);
		$senderid="GRNWAV";	
		$url = "https://msg.hypecreationz.com/vb/apikey.php?apikey=$key&senderid=$senderid&number=$mbl&message=$message_content";
							
		$output = file_get_contents($url);	
    }

    function cashDiscounts(){
    	return array(
                array('label'=>'Advance','discount'=>3.0),
                array('label'=>'7 days','discount'=>2.0)
            );
    }

    function fetchProducts($productType){
    	$products = \App\Product::where('is_trader_product',$productType)->get()->toArray();
    	return $products;
    }

    function validateOtp($otp,$module,$mobile){
    	$details = \App\RequestOtp::where([
    		'otp' => $otp,
    		'module' => $module,
    		'mobile' => $mobile,
    	])->orderby('id','desc')->first();
    	if(is_object($details)){
    		$details->delete(); //delete record after verification
    		return true;
    	}

    	return false;
    }
?>