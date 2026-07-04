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
use App\RegisterRequest;
use App\Product;
use App\CustomerRegisterRequest;
use App\Customer;
use App\CustomerDiscount;
use App\UserCustomerShare;
use App\CustomerCity;
use App\CustomerEmployee;
use App\Discount;
use Validator;
class CustomerController extends Controller
{
    //
    public function customers(Request $Request)
    {
        Session::put('active', 'customers');

        if ($Request->ajax()) {

            $data = $Request->input();

            // ── Base query builder (reused for counts + rows) ─────
            $buildQuery = function () use ($data) {
                $q = Customer::query();

                if (!empty($data['name'])) {
                    $q->where('name', 'like', '%' . $data['name'] . '%');
                }
                if (!empty($data['city_name'])) {
                    $q->whereHas('cities', function ($c) use ($data) {
                        $c->where('city_name', 'like', '%' . $data['city_name'] . '%');
                    });
                }
                if (!empty($data['email_status']) && $data['email_status'] !== 'All') {
                    if ($data['email_status'] === 'tick') {
                        $q->where('email', '!=', '')->whereNotNull('email');
                    } elseif ($data['email_status'] === 'cross') {
                        $q->where(function ($e) { $e->whereNull('email')->orWhere('email', ''); });
                    }
                }
                if (!empty($data['b_card_status']) && $data['b_card_status'] !== 'All') {
                    if ($data['b_card_status'] === 'tick') {
                        $q->where(function ($b) {
                            $b->where(function ($s) { $s->whereNotNull('business_card')->where('business_card', '!=', ''); })
                              ->orWhere(function ($s) { $s->whereNotNull('business_card_two')->where('business_card_two', '!=', ''); });
                        });
                    } elseif ($data['b_card_status'] === 'cross') {
                        $q->where(function ($b) {
                            $b->where(function ($s) { $s->whereNull('business_card')->orWhere('business_card', ''); })
                              ->where(function ($s) { $s->whereNull('business_card_two')->orWhere('business_card_two', ''); });
                        });
                    }
                }
                if (!empty($data['status']) && $data['status'] !== 'All') {
                    $q->where('customers.status', $data['status'] === 'Active' ? 1 : 0);
                }
                if (!empty($data['business_linking']) && $data['business_linking'] !== 'All') {
                    if (in_array($data['business_linking'], ['Open', 'Direct Customer', 'Hybrid'])) {
                        $q->where('business_model', $data['business_linking']);
                    } elseif (is_numeric($data['business_linking'])) {
                        $q->where('dealer_id', $data['business_linking']);
                    }
                }
                if (!empty($data['linked_executive']) && $data['linked_executive'] !== 'All') {
                    $q->whereHas('user_customer_shares.user', function ($u) use ($data) {
                        $u->where('name', 'like', '%' . $data['linked_executive'] . '%');
                    });
                }
                return $q;
            };

            // ── Breakdown counts (respect current filters) ────────
            $countBase   = $buildQuery();
            $totalCount  = (clone $countBase)->count();
            $directCount = (clone $countBase)->where('business_model', 'Direct Customer')->count();
            $openCount   = (clone $countBase)->where('business_model', 'Open')->count();
            $hybridCount = (clone $countBase)->where('business_model', 'Hybrid')->count();
            $dealerCount = (clone $countBase)->whereNotNull('dealer_id')->count();

            // ── Paginated rows ────────────────────────────────────
            $customers = $buildQuery()
                ->with(['cities', 'dealer', 'user_customer_shares.user'])
                ->orderBy('customers.id', 'DESC')
                ->paginate(25);

            $rows = [];
            foreach ($customers as $customer) {

                if (!empty($customer->dealer->business_name)) {
                    $linking = '<span class="badge-linking badge-dealer">' . e($customer->dealer->business_name) . '</span>';
                } elseif ($customer->business_model === 'Open') {
                    $linking = '<span class="badge-linking badge-open">Open</span>';
                } elseif ($customer->business_model === 'Direct Customer') {
                    $linking = '<span class="badge-linking badge-direct">Direct Customer</span>';
                } elseif ($customer->business_model === 'Hybrid') {
                    $linking = '<span class="badge-linking badge-hybrid">Hybrid</span>';
                } else {
                    $linking = '<span class="badge-linking badge-muted">' . e($customer->business_model) . '</span>';
                }

                $execs = [];
                foreach ($customer->user_customer_shares as $share) {
                    if (!empty($share->user->name)) $execs[] = $share->user->name;
                }

                $rows[] = [
                    'id'        => $customer->id,
                    'name'      => ucwords($customer->name),
                    'city'      => $customer->cities->pluck('city_name')->implode(', '),
                    'linking'   => $linking,
                    'executive' => implode(', ', $execs),
                    'email'     => $customer->email != '' ? 1 : 0,
                    'b_card'    => (!empty($customer->business_card) || !empty($customer->business_card_two)) ? 1 : 0,
                    'status'    => $customer->status == 1 ? 1 : 0,
                    'edit_url'  => url('/admin/add-edit-customer/' . $customer->id),
                ];
            }

            return response()->json([
                'data'         => $rows,
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'total'        => $customers->total(),
                'from'         => $customers->firstItem(),
                'to'           => $customers->lastItem(),
                'counts'       => [
                    'total'  => $totalCount,
                    'direct' => $directCount,
                    'open'   => $openCount,
                    'hybrid' => $hybridCount,
                    'dealer' => $dealerCount,
                ],
            ]);
        }

        $title         = "Customers";
        $linkedDealers = \App\Dealer::where('status', 1)->whereNull('parent_id')
                            ->select('id', 'business_name')->orderBy('business_name')->get();
        $executives    = \App\Helpers\EmployeeHelper::getEmployeesWithCustomers();

        return View::make('admin.customers.customers')->with(compact('title', 'linkedDealers', 'executives'));
    }

    public function toggleStatus(Request $request)
    {
        $id     = $request->input('id');
        $status = (int) $request->input('status'); // 1 = Active, 0 = Inactive

        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer not found'], 404);
        }

        $customer->status = $status;
        $customer->save();

        return response()->json([
            'status'   => true,
            'message'  => 'Status updated successfully',
            'new_value'=> $customer->status,
        ], 200);
    }
    
    /**
     * ─────────────────────────────────────────────────────────────────────
     *  CustomerController — UPDATED METHODS for the add/edit revamp
     *  Replace the corresponding methods in
     *  app/Http/Controllers/Admin/CustomerController.php with these.
     *
     *  Summary of changes:
     *   • "Products Under Discounts Model" removed — discount_products are no
     *     longer read or saved; only discount_type = 'net_products' rows are
     *     touched, so any legacy 'discounts' rows are left intact.
     *   • New business model 'Hybrid' — requires dealer_id AND uses the
     *     Products / Payment Term / Freight sections (same as Direct Customer).
     *   • Payment Terms is now a dropdown (payment_terms() helper) plus
     *     freight_basis + freight columns on customers.
     *   • net_products payload is now an indexed array of structured rows
     *     (see the blade) with a Conditional Special Price/Discount block.
     *   • fetchProductsByType now returns each product's Standard DP so the
     *     screen's Viability Check can recalculate live.
     * ─────────────────────────────────────────────────────────────────────
     */

    // ── addEditCustomer ──────────────────────────────────────────────────
    public function addEditCustomer(Request $request, $customerid = NULL)
    {
        $userids = \DB::table('user_departments')->where('department_id', 2)->pluck('user_id')->toArray();
        $users   = \DB::table('users')->wherein('id', $userids)->get();
        $users   = json_decode(json_encode($users), true);

        $selCities           = array();
        $existingNetProducts = array();
        $requestReceivedFrom = "";
        $customerCreatedBy   = "";

        if (!empty($customerid)) {
            $customerdata = Customer::with(['cities', 'employees' => function ($query) {
                $query->where('is_delete', 0);
            }, 'user_customer_shares', 'customer_register_request', 'creator'])->where('id', $customerid)->first();
            $customerdata = json_decode(json_encode($customerdata), true);
            $selCities    = array_column($customerdata['cities'], 'city_name');

            // "Products" section rows (formerly Net Price Model). Discounts-model rows are no longer loaded.
            $existingNetProducts = CustomerDiscount::where('customer_id', $customerid)
                ->where('discount_type', 'net_products')
                ->join('products', 'products.id', '=', 'customer_discounts.product_id')
                ->select('customer_discounts.*', 'products.product_name')
                ->get();

            $requestReceivedFrom = $customerdata['customer_register_request']['creator']['name'] ?? '';
            $customerCreatedBy   = $customerdata['creator']['name'] ?? '';
            $title = "Edit Customer";
        } else {
            $title        = "Add Customer";
            $customerdata = array();
        }

        if (isset($_GET['ref'])) {
            $customerdata = RegisterRequest::select('business_name as name', 'city', 'name as contact_person_name', 'email', 'mobile', 'city')->where('id', $_GET['ref'])->first();
            $customerdata = json_decode(json_encode($customerdata), true);
            $selCities    = array(strtoupper($customerdata['city']));
        }
        if (isset($_GET['empref'])) {
            $registerRequestData = CustomerRegisterRequest::with('creator')->where('id', $_GET['empref'])->first();
            $registerRequestData = json_decode(json_encode($registerRequestData), true);
            $customerdata['name']                  = $registerRequestData['name'];
            $customerdata['address']               = $registerRequestData['address'];
            $customerdata['activity']              = $registerRequestData['activity'];
            $customerdata['contact_person_name']   = $registerRequestData['contact_person_name'];
            $customerdata['designation']           = $registerRequestData['designation'];
            $customerdata['mobile']                = $registerRequestData['mobile'];
            $customerdata['email']                 = $registerRequestData['email'];
            $customerdata['business_model']        = $registerRequestData['business_model'];
            $customerdata['customer_product_type'] = $registerRequestData['customer_product_type'];
            $customerdata['dealer_id']             = $registerRequestData['dealer_id'];
            $customerdata['linked_executive']      = $registerRequestData['linked_executive'];
            $customerdata['business_card_url']     = $registerRequestData['business_card_url'];
            $customerdata['business_card_two_url'] = $registerRequestData['business_card_two_url'];
            $customerdata['created_at']            = date('Y-m-d', strtotime($registerRequestData['created_at']));
            $selCities           = array(strtoupper($registerRequestData['cities']));
            $requestReceivedFrom = $registerRequestData['creator']['name'] ?? '';
        }

        return view('admin.customers.add-edit-customer')->with(compact(
            'title', 'customerdata', 'selCities', 'users',
            'existingNetProducts', 'requestReceivedFrom', 'customerCreatedBy'
        ));
    }

    // ── saveCustomer ─────────────────────────────────────────────────────
    public function saveCustomer(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = $request->all();
                if ($data['customerid'] == "") {
                    $type        = "add";
                    $emailunique  = "unique:customers,email";
                    $mobileunique = "unique:customers,mobile";
                } else {
                    $type        = "update";
                    $emailunique  = "unique:customers,email," . $data['customerid'];
                    $mobileunique = "unique:customers,mobile," . $data['customerid'];
                }

                $validator = Validator::make($request->all(), [
                    'name'           => 'bail|required',
                    'email'          => 'bail|nullable|email|' . $emailunique,
                    'mobile'         => 'bail|required|numeric|digits:10|' . $mobileunique,
                    'business_model' => 'bail|required',
                    // Dealer link is required for both Dealer and the new Hybrid model
                    'dealer_id'      => 'bail|required_if:business_model,Dealer,Hybrid|nullable',
                    // Payment terms required whenever the Products section applies
                    'payment_term'   => 'bail|required_if:business_model,Direct Customer,Hybrid|nullable',
                    'freight_basis'  => 'bail|required_if:business_model,Direct Customer,Hybrid|nullable',
                    'net_products.*.product_id'            => 'bail|nullable|exists:products,id',
                    'net_products.*.customer_selling_price'=> 'bail|nullable|numeric|min:0',
                ]);

                if ($validator->passes()) {
                    $data = $request->all();

                    // Guard: same product must not appear twice in the Products section
                    $netProducts = array_values($data['net_products'] ?? []);
                    $netIds      = array_filter(array_column($netProducts, 'product_id'));
                    if (count($netIds) !== count(array_unique($netIds))) {
                        $dupIds   = array_unique(array_diff_assoc($netIds, array_unique($netIds)));
                        $dupNames = Product::whereIn('id', $dupIds)->pluck('product_name')->toArray();
                        return response()->json([
                            'status' => false,
                            'errors' => ['linking_error' => [
                                'These products are added more than once: ' . implode(', ', $dupNames)
                            ]],
                        ]);
                    }

                    if ($type == "add") {
                        $customer = new Customer;
                        $customer->created_by = auth()->user()->id;
                    } else {
                        $customer = Customer::find($data['customerid']);
                    }

                    $customer->customer_product_type = $data['customer_product_type'];
                    $customer->name                  = $data['name'];
                    $customer->contact_person_name   = $data['contact_person_name'];
                    $customer->designation           = $data['designation'];
                    $customer->department            = getDepartmentByDesignation($data['designation']);
                    $customer->business_model        = $data['business_model'];
                    $customer->address               = $data['address'];
                    $customer->activity              = isset($data['activity']) ? implode(',', $data['activity']) : "";
                    $customer->mobile                = $data['mobile'];
                    $customer->email                 = $data['email'];
                    $customer->status                = $data['status'];

                    if (!empty($data['dealer_id'])) {
                        $customer->dealer_id = $data['dealer_id'];
                    }

                    // ── Business-model specific fields ────────────────────
                    $customer->payment_term_type = "";
                    $customer->freight_basis     = null;
                    $customer->freight           = null;

                    if ($data['business_model'] == "Open") {
                        $customer->dealer_id = NULL;
                    } elseif ($data['business_model'] == "Dealer") {
                        // dealer_id set above; no direct pricing terms
                    } elseif (in_array($data['business_model'], ["Direct Customer", "Hybrid"])) {
                        if ($data['business_model'] == "Direct Customer") {
                            $customer->dealer_id = NULL; // Hybrid keeps its dealer link
                        }
                        $customer->payment_term_type = 'On Bill';
                        $customer->payment_term      = $data['payment_term'] ?? '';
                        $customer->freight_basis     = $data['freight_basis'] ?? 'Paid by Company';
                        $customer->freight           = ($customer->freight_basis == 'Paid by Company')
                            ? ($data['freight'] ?? 0) : 0;
                    }

                    $customer->location_address = $data['location_address'];
                    if ($data['location_address'] == "") {
                        $customer->location_address = NULL;
                        $customer->latitude  = NULL;
                        $customer->longitude = NULL;
                    }
                    $customer->custom_mtod_from     = 0;
                    $customer->custom_mtod_to       = 0;
                    $customer->custom_mtod_discount = 0;

                    $registerRequestInfo = null;
                    if (isset($data['customer_register_request_id'])) {
                        $registerRequestInfo = CustomerRegisterRequest::where('id', $data['customer_register_request_id'])->first();
                        $customer->customer_register_request_id = $data['customer_register_request_id'];
                    }

                    if ($request->hasFile('business_card')) {
                        $file     = $request->file('business_card');
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path     = 'business_cards/' . $filename;
                        $file->move(public_path('business_cards'), $filename);
                        $customer->business_card = $path;
                    } elseif (is_object($registerRequestInfo)) {
                        $customer->business_card = $registerRequestInfo->business_card;
                    }
                    if ($request->hasFile('business_card_two')) {
                        $file     = $request->file('business_card_two');
                        $filename = time() . '2_' . $file->getClientOriginalName();
                        $path     = 'business_cards/' . $filename;
                        $file->move(public_path('business_cards'), $filename);
                        $customer->business_card_two = $path;
                    } elseif (is_object($registerRequestInfo)) {
                        $customer->business_card_two = $registerRequestInfo->business_card_two;
                    }

                    $customer->save();

                    // ── Products section — replace only net_products rows.
                    //    (Legacy 'discounts' rows are intentionally left alone.)
                    DB::table('customer_discounts')
                        ->where('customer_id', $customer->id)
                        ->where('discount_type', 'net_products')
                        ->delete();

                    if (in_array($data['business_model'], ["Direct Customer", "Hybrid"])) {
                        foreach ($netProducts as $row) {
                            if (empty($row['product_id'])) continue;

                            $hasSpecial = (($row['has_special'] ?? 'no') === 'yes');

                            $custDis = new CustomerDiscount();
                            $custDis->customer_id   = $customer->id;
                            $custDis->discount_type = 'net_products';
                            $custDis->product_id    = $row['product_id'];

                            // renamed fields → existing columns
                            $custDis->net_price    = $row['customer_selling_price'] ?? 0; // Customer Selling Price
                            $custDis->moq          = $row['moq'] ?? 0;                    // MOQ (kg)
                            $custDis->packing_type = $row['packing_size'] ?? 'Standard';  // Packing Size

                            // new columns
                            $custDis->selling_expense_basis = $row['selling_expense_basis'] ?? '%';
                            $custDis->selling_expense_value = $row['selling_expense_value'] !== '' ? ($row['selling_expense_value'] ?? null) : null;
                            $custDis->has_special           = $hasSpecial ? 'yes' : 'no';

                            // Conditional Special Price / Special Discount block
                            if ($hasSpecial) {
                                $custDis->for_qty         = $row['special_moq'] ?? null;   // For MOQ (kg)
                                $custDis->applicable_type = $row['special_basis'] ?? null; // Special Price | Special Discount
                                $custDis->value           = $row['special_value'] ?? null;
                                $custDis->special_selling_expense_basis = $row['special_selling_expense_basis'] ?? null;
                                $custDis->special_selling_expense_value = $row['special_selling_expense_value'] !== '' ? ($row['special_selling_expense_value'] ?? null) : null;
                            }
                            $custDis->save();
                        }
                    }

                    // ── Cities ────────────────────────────────────────────
                    DB::table('customer_cities')->where('customer_id', $customer->id)->delete();
                    foreach ($data['cities'] as $cityInfo) {
                        $custCity              = new CustomerCity;
                        $custCity->customer_id = $customer->id;
                        $custCity->city_name   = $cityInfo;
                        $custCity->save();
                    }

                    // ── Add-on users (unchanged behaviour) ────────────────
                    if (isset($data['names'])) {
                        foreach ($data['names'] as $nkey => $custEmp) {
                            if (isset($data['cust_emp_id'][$nkey]) && !empty($data['cust_emp_id'][$nkey])) {
                                $savecustEmp = CustomerEmployee::find($data['cust_emp_id'][$nkey]);
                                if (isset($data['is_delete'][$nkey])) {
                                    $savecustEmp->is_delete = $data['is_delete'][$nkey];
                                }
                            } else {
                                $savecustEmp              = new CustomerEmployee;
                                $savecustEmp->customer_id = $customer->id;
                            }
                            $savecustEmp->name        = $custEmp;
                            $savecustEmp->mobile      = $data['mobiles'][$nkey];
                            $savecustEmp->email       = $data['emails'][$nkey];
                            $savecustEmp->designation = $data['designations'][$nkey];
                            $savecustEmp->save();
                        }
                    }

                    // ── Linked executives (unchanged behaviour) ───────────
                    UserCustomerShare::where('customer_id', $customer->id)->delete();
                    if (isset($data['marketing_user_ids'])) {
                        foreach ($data['marketing_user_ids'] as $ukey => $marketuser) {
                            $user_cust_share              = new UserCustomerShare;
                            $user_cust_share->user_id     = $marketuser;
                            $user_cust_share->customer_id = $customer->id;
                            $user_cust_share->share       = 100;
                            $user_cust_share->user_date   = $data['user_dates'][$ukey];
                            $user_cust_share->save();
                        }
                    }

                    if (isset($data['register_request_id'])) {
                        RegisterRequest::where('id', $data['register_request_id'])->delete();
                    }

                    if (isset($data['customer_register_request_id'])) {
                        $customerId = $customer->id;
                        CustomerRegisterRequest::where('id', $data['customer_register_request_id'])->update(['status' => 'Added']);
                        \App\CustomerContact::where('customer_register_request_id', $data['customer_register_request_id'])
                            ->update([
                                'customer_id'                  => $customerId,
                                'customer_register_request_id' => null,
                            ]);
                        \App\UserScheduler::where('customer_register_request_id', $data['customer_register_request_id'])
                            ->update([
                                'customer_id'                  => $customerId,
                                'customer_register_request_id' => null,
                            ]);
                        \App\WorkNote::where('customer_register_request_id', $data['customer_register_request_id'])
                            ->update([
                                'customer_id'                  => $customerId,
                                'customer_register_request_id' => null,
                            ]);
                    }

                    $redirectTo = url('/admin/customers?s');
                    return response()->json(['status' => true, 'message' => 'ok', 'url' => $redirectTo]);
                } else {
                    return response()->json(['status' => false, 'errors' => $validator->messages()]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'errors' => array('value' => $e->getMessage())]);
        }
    }


    public function appedDiscountDetails(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		return response()->json([
                'view' => (String)View::make('admin.customers.append-discount-details')->with(compact('data')),
            ]);
    	}
    }

    public function addCustomerDiscount(Request $request){
    	if($request->ajax()){
    		$validator = Validator::make($request->all(), [
	                'discount_type'     =>  'bail|required',
	                'product_id' => 'bail|required_if:discount_type,==,Product Base|nullable',
	                'from_qty' => 'bail|required_if:discount_type,==,Product Base|nullable',
                    'to_qty' => 'bail|required_if:discount_type,==,Product Base|nullable',
	                /*'company_share' =>  'bail|numeric|between:0,100',
	                'dealer_share' =>  'bail|numeric|between:0,100',*/
	            ]
	        );
	        if($validator->passes()) {
	        	$data = $request->all();
                if($data['discount_type'] == 'Turnover'){
                    $sumCustDealer = $data['company_share'] + $data['dealer_share'];
    	        	if($sumCustDealer ==100){
    	        		$discountinfo = $data;
    		            return response()->json([
    		            	'status' => true,
    		                'view' => (String)View::make('admin.customers.customer-discount-list')->with(compact('discountinfo')),
    		            ]);
    	        	}else{
    	        		return response()->json(['status'=>false,'errors'=>array('dealer_share'=> array('Sum of Customer and Dealer share must be 100%'))]);
    	        	}
                }else{
                    $discountinfo = $data;
                    return response()->json([
                        'status' => true,
                        'view' => (String)View::make('admin.customers.customer-discount-list')->with(compact('discountinfo')),
                    ]);
                }
	        }else{
                return response()->json(['status'=>false,'errors'=>$validator->messages()]);
            }
    	}
    }

    public function customerDiscounts(Request $Request){
        Session::put('active','customerdiscounts'); 
        $discounts = Discount::orderby('start_date','DESC')->groupby('start_date')->pluck('start_date')->toArray();
        //echo "<pre>"; print_r($discounts); die;
        /*if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Discount::query();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('discounts.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $discount){ 
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-customer-discount/'.$discount['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a style="display:none;" title="Clone" class="btn btn-sm yellow margin-top-10" href="'.url('/admin/add-edit-customer-discount/?type=clone&id='.$discount['id']).'"> <i class="fa fa-plus"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $discount['id'],
                    date('M Y',strtotime($discount['start_date'])),
                    "Rs. " .$discount['range_from'],
                    "Rs. " .$discount['range_to'],
                    $discount['discount']."%", 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }*/
        $title = "Turnover Discounts";
        return View::make('admin.customers.customer-discounts')->with(compact('title','discounts'));
    }

    public function addEditCustomerDiscount(Request $request,$discountid=NULL){
        $getLastDiscount = array();
        if(!empty($discountid)){
            $discountdata = Discount::where('id',$discountid)->first();
            $title ="Edit Turnover Discount";
        }else{
            /*if(isset($_GET['type']) && isset($_GET['id']) && $_GET['type'] =='clone'){
                $custDiscount =  Discount::find($_GET['id']);
                $getLastDiscount = Discount::whereDate('start_date',$custDiscount->start_date)->where('end_date',$custDiscount->end_date)->orderby('id','DESC')->first();
                $getLastDiscount = json_decode(json_encode($getLastDiscount),true);
            }*/
            $title ="Add Turnover Discount";
            $discountdata =array();
        }
        return view('admin.customers.add-edit-customer-discount')->with(compact('title','discountdata','getLastDiscount','discountid'));
    }

    public function saveCustomerDiscount(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['discountid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    /*'start_date' => 'bail|required|date_format:Y-m-d',
                    'end_date' => 'bail|required|date_format:Y-m-d',*/
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gte:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $discount = new Discount; 
                    }else{
                        $discount = Discount::find($data['discountid']);
                        //Update Next Slot
                        /*$getNextDis = Discount::whereDate('start_date',$data['start_date'])->where('end_date',$data['end_date'])->where('range_from',$discount->range_to +1)->first();
                        $getNextDis = json_decode(json_encode($getNextDis),true);
                        if($getNextDis){
                            if($getNextDis['range_to'] > ($data['range_to'] +1) ){
                                DB::table('discounts')->where('id',$getNextDis['id'])->update(['range_from'=>$data['range_to']+1]);
                            }else{
                                return response()->json(['status'=>false,'errors'=>array('range_to'=>array('Range To value cannot be updated becuase its exceeded for next slot'))]);
                            }
                        }*/
                    }
                    $discount->month = $data['month'];
                    $discount->year = $data['year'];
                    $start_date = $data['year'].'-'.$data['month']."-01";
                    $discount->start_date = date('Y-m-d',strtotime($start_date));
                    $discount->range_from = $data['range_from'];
                    $discount->range_to   = $data['range_to'];
                    $discount->discount   = $data['discount'];
                    $discount->save();
                    $redirectTo = url('/admin/customer-discounts?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function deleteCustomerDiscount($disid){
        Discount::where('id',$disid)->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function appendMarketingUsers(Request $request){
        $userids = \DB::table('user_departments')->where('department_id',2)->pluck('user_id')->toArray();
        $users  = \DB::table('users')->wherein('id',$userids)->get();
        $users = json_decode(json_encode($users),true);
        return response()->json([
                'view' => (String)View::make('admin.customers.append-marketing-users')->with(compact('users')),
            ]);
    }

    public function registerRequests(Request $Request){
        Session::put('active','registerRequests'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = RegisterRequest::query();
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            if(!empty($data['business_name'])){
                $querys = $querys->where('business_name','like','%'.$data['business_name'].'%');
            }
            if(!empty($data['email'])){
                $querys = $querys->where('email','like','%'.$data['email'].'%');
            }
            if(!empty($data['mobile'])){
                $querys = $querys->where('mobile',$data['mobile']);
            }
            if(!empty($data['city'])){
                $querys = $querys->where('city',$data['city']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('register_requests.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $regis_req){ 
                $actionValues='
                    <a title="Create Customer" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-customer?ref='.$regis_req['id']).'"> Create Customer
                    </a>
                     <a title="Delete Request" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-register-request/'.$regis_req['id']).'"><i class="fa fa-times"></i></a>';
                $num = ++$i;
                $records["data"][] = array(      
                    ucwords($regis_req['business_name']),  
                    ucwords($regis_req['name']),  
                    ucwords($regis_req['email']),  
                    ucwords($regis_req['mobile']),  
                    ucwords($regis_req['city']),  
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Register Requests";
        return View::make('admin.customers.register-requests')->with(compact('title'));
    }

    public function deleteRegisterRequest($id){
        RegisterRequest::where('id',$id)->delete();
        return redirect()->back()->with('flash_message_success','Register request has been deleted successfully');
    }

    public function fetchProductsByType(Request $request)
    {
        $type = $request->get('type');

        $products = fetchProducts($type);

        return response()->json($products);
    }
}
