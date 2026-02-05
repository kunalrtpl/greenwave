<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Sampling;
use App\SamplingItem;
use App\SamplingSaleInvoice;
use App\DealerProduct;
use App\FreeSamplingStock;
use App\UserFreeSampleStock;
use App\Product;
use Session;
use DB;
use PDF;
use Carbon\Carbon;
class SamplingController extends Controller
{
    //
    public function freeSampling(Request $request)
    {
        Session::put('active', 'freeSampling');

        if ($request->ajax()) {

            $data = $request->input();

            /* ================= BASE QUERY ================= */
            $querys = Sampling::with([
                    'sampleitems.product',
                    'sampleitems.sale_invoice_items'
                ])
                ->leftJoin('sampling_items', 'sampling_items.sampling_id', '=', 'samplings.id')
                ->leftJoin('products', 'products.id', '=', 'sampling_items.product_id')
                ->leftJoin('users', 'users.id', '=', 'samplings.user_id')
                ->leftJoin('customers', 'customers.id', '=', 'samplings.customer_id')
                ->where('samplings.sample_type', 'free')
                ->select(
                    'samplings.*',
                    'users.name as executive_name',
                    'users.email as executive_email',
                    'customers.name as customer_name'
                )
                ->groupBy('samplings.id');

            /* ================= FILTERS ================= */

            // Sample Ref No
            if (!empty($data['sample_ref_no_string'])) {
                $querys->where('samplings.sample_ref_no_string', $data['sample_ref_no_string']);
            }

            // Status
            if (!empty($data['status'])) {
                if ($data['status'] == "completed") {
                    $querys->whereIn('samplings.sample_status', ['executed', 'completed']);
                } else {
                    $querys->where('samplings.sample_status', $data['status']);
                }
            }

            // User Type
            if (!empty($data['user_type'])) {
                $querys->where('samplings.action', 'like', '%' . $data['user_type'] . '%');
            }

            // ✅ CUSTOMER SEARCH
            if (!empty($data['customer_info'])) {
                $keyword = $data['customer_info'];
                $querys->where('customers.name', 'like', '%' . $keyword . '%');
            }

            // ✅ PRODUCT NAME SEARCH (MAIN REQUIREMENT)
            if (!empty($data['product_name'])) {
                $keyword = $data['product_name'];
                $querys->where('products.product_name', 'like', '%' . $keyword . '%');
            }

            /* ================= DATATABLES ================= */

            $iDisplayLength = intval($request->input('length'));
            $iDisplayStart  = intval($request->input('start'));
            $sEcho          = intval($request->input('draw'));

            $iTotalRecords = $querys->distinct('samplings.id')->count('samplings.id');

            $results = $querys
                ->orderBy('samplings.id', 'DESC')
                ->skip($iDisplayStart)
                ->take($iDisplayLength)
                ->get();

            /* ================= RESPONSE ================= */

            $records = [];
            $records["data"] = [];

            foreach ($results as $sampleReq) {

                /* ---------- STATUS TEXT ---------- */
                $statusText = ucwords($sampleReq->sample_status);

                if ($sampleReq->sample_status == "pending") {
                    $statusText = "<b style='color:red;'>Pending Confirmation</b>";
                } elseif ($sampleReq->sample_status == "approved") {
                    $statusText = "<b>Pending Dispatch</b>";
                } elseif (in_array($sampleReq->sample_status, ['executed', 'completed'])) {
                    $statusText = "<b style='color:green;'>Completed</b>";
                }

                /* ---------- PRODUCTS TABLE ---------- */
                $products = '';
                if ($sampleReq->sampleitems->count()) {

                    $products .= '<table class="table table-bordered">
                        <tr>
                            <th>Product <br><small>(Pack Size)</small></th>
                            <th>RQ</th>
                            <th>AQ</th>
                            <th>PQ</th>
                        </tr>';

                    foreach ($sampleReq->sampleitems as $item) {

                        $itemAction = '';
                        if ($item->item_action == "On Hold") {
                            $itemAction = '<span class="badge badge-warning">On Hold</span>';
                        } elseif ($item->item_action == "Cancel") {
                            $itemAction = '<span class="badge badge-dark">Cancel</span>';
                        } elseif ($item->item_action == "Urgent") {
                            $itemAction = '<span class="badge badge-danger">Urgent</span>';
                        }

                        $saleQty = $item->sale_invoice_items
                            ? array_sum(array_column($item->sale_invoice_items->toArray(), 'qty'))
                            : 0;

                        $pendingQty = $item->actual_qty - $saleQty;
                        if ($pendingQty <= 0) {
                            $itemAction = '';
                        }

                        $products .= '<tr>
                            <td>'.$item->product->product_name.'
                                <br><small>('.$item->actual_pack_size.' kg)</small>
                                '.$itemAction.'
                            </td>
                            <td>'.$item->qty.' kg</td>
                            <td>'.$item->actual_qty.' kg</td>
                            <td>'.$pendingQty.' kg</td>
                        </tr>';
                    }

                    $products .= '</table>';
                }

                /* ---------- ACTION BUTTONS ---------- */
                $actionValues = '
                    <a href="'.route('sampling.download.pdf', $sampleReq->id).'" class="btn btn-sm blue">PDF</a>
                    <a style="display:none;" target="_blank" class="btn btn-sm green margin-top-10"
                       href="'.url('admin/free-sampling-detail/'.$sampleReq->id).'">View</a>
                    <a target="_blank" class="btn btn-sm yellow margin-top-10"
                       href="'.url('admin/view-sampling/'.$sampleReq->id).'">View</a>
                ';

                /* ---------- ROW ---------- */
                $records["data"][] = [
                    $sampleReq->sample_ref_no_string.'<br><small>('.
                    date('d M Y', strtotime($sampleReq->created_at)).')</small>',
                    ucwords($sampleReq->executive_name),
                    ucwords($sampleReq->customer_name),
                    $products,
                    $sampleReq->remarks,
                    $statusText,
                    $actionValues
                ];
            }

            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;

            return response()->json($records);
        }

        $title = "Sample Requests";
        return view('admin.samplings.free.index', compact('title'));
    }


    

    public function viewSampling($id)
    {
        $sampleDetails = Sampling::with([
            'customer',
            'user',
            'sampleitems.requested_product',
            'sampleitems.product'
        ])->findOrFail($id);
        //echo "<pre>"; print_r($sampleDetails->toArray()); die;
        /* 
         | Load ONLY ACTIVE products
         | Along with their LATEST dealer price (no future)
        */
        $products = Product::where('status', 1)
            ->with(['pricings' => function ($q) {
                $q->whereDate('price_date', '<=', Carbon::today())
                  ->orderBy('price_date', 'desc');
            }])
            ->orderByDesc('id')
            ->get();

        /*
         | Attach dealer_price to each product (computed attribute)
        */
        $products->each(function ($product) {
            $product->dealer_price = optional($product->pricings->first())->dealer_price ?? 0;
        });
        $title = "View Sampling";
        return view('admin.samplings.free.show', compact(
            'sampleDetails',
            'products',
            'title'
        ));
    }


    public function paidSampling(Request $Request){
        Session::put('active','paidSampling'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Sampling::with(['sampleitems'])->leftjoin('dealers','dealers.id','=','samplings.dealer_id')->where('sample_type','paid')->select('samplings.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email');
            if(!empty($data['id'])){
                $querys = $querys->where('samplings.sample_ref_no_string',$data['sample_ref_no_string']);
            }
            if(!empty($data['status'])){
                if($data['status'] == "completed"){
                    $querys = $querys->wherein('samplings.sample_status',['executed','completed']);
                }else{
                    $querys = $querys->where('samplings.sample_status',$data['status']);
                }
            }
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                  });
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('samplings.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $sampleReq){ 
                $actionValues='<a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/paid-sampling-detail/'.$sampleReq['id']).'"> View
                    </a>';
                $userInfo = "";
                if(!empty($sampleReq['business_name'])){
                    $userInfo = ucwords($sampleReq['business_name']);
                }
                if(!empty($sampleReq['executive_name'])){
                    $userInfo = ucwords($sampleReq['executive_name']);
                }
                if($sampleReq['sample_status'] =="pending"){
                   $sampleReq['sample_status'] = "<b style='color:red;'>Pending Confirmation</b>"; 
                }elseif($sampleReq['sample_status'] =="approved"){
                   $sampleReq['sample_status'] = "<b>Pending Dispatch</b>"; 
                }elseif($sampleReq['sample_status'] =="executed"){
                    $sampleReq['sample_status'] = "<b style='color:green;'>Completed</b>"; 
                }elseif($sampleReq['sample_status'] =="completed"){
                    $sampleReq['sample_status'] = "<b style='color:green;'>Completed</b>";
                    if(empty($sampleReq['saleinvoices'])){
                        if(!empty($sampleReq['adjust_items'])){
                           $sampleReq['sample_status'] = 'Adjusted'; 
                        }else if(!empty($sampleReq['cancel_items'])){
                           $sampleReq['sample_status'] = 'Cancelled'; 
                        }
                    }else{
                        if(!empty($sampleReq['adjust_items'])){
                           $sampleReq['sample_status'] .= '<br><small> Partially Adjusted</small>'; 
                        }else if(!empty($sampleReq['cancel_items'])){
                           $sampleReq['sample_status'] .= '<br><small>Partially Cancelled</small>'; 
                        }
                    }
                }
                if($sampleReq['sampleitems']){
                    $products =  '<table class="table table-bordered">
                                    <tr>
                                        <th>Product<br><small>(Pack Size)</small></th>
                                        <th>RQ</th>
                                        <th>AQ</th>
                                        <th>PQ</th>
                                    </tr>';
                    foreach($sampleReq['sampleitems'] as $sampleitem){
                        $item_action = "";
                        if($sampleitem['item_action']=="On Hold"){
                            $item_action = '<span class="badge badge-warning">'.$sampleitem['item_action'].'</span>';
                        }else if($sampleitem['item_action']=="Cancel"){
                            $item_action = '<span class="badge badge-dark">'.$sampleitem['item_action'].'</span>';
                        }else if($sampleitem['item_action']=="Urgent"){
                            $item_action = '<span class="badge badge-danger">'.$sampleitem['item_action'].'</span>';
                        }
                        $sale_invoice_qty = array_sum(array_column($sampleitem['sale_invoice_items'],'qty'));
                        if(empty($sale_invoice_qty)){
                            $sale_invoice_qty = 0;
                        }
                        $pending_qty = $sampleitem['actual_qty'] - $sale_invoice_qty;
                        //$pending_qty = $sampleitem['actual_qty'];
                        if($pending_qty ==0){
                            $item_action = "";
                        }
                        $products .= '<tr>
                                        <td>'.$sampleitem['product']['product_name'].'<br><small>('.$sampleitem['actual_pack_size'].'kg Packing)</small>'.$item_action.'</td>
                                        <td>'.$sampleitem['qty'].'kg</td>
                                        <td>'.$sampleitem['actual_qty'].'kg</td>
                                        <td>'.$pending_qty.'kg</td>
                                    </tr>';
                    }
                    $products .='</table>';
                }
                $records["data"][] = array( 
                    $sampleReq['sample_ref_no_string'].'<br><small>('.
                    date('d M Y',strtotime($sampleReq['created_at'])).')</small>',
                    $userInfo,
                    $sampleReq['request_type'],
                    $products,
                    $sampleReq['remarks'],
                    ucwords($sampleReq['sample_status']).'<br><small>('.ucwords($sampleReq['required_through']).')</small>',
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Paid Sample Requests";
        return View::make('admin.samplings.paid-sampling')->with(compact('title'));
    }

    public function freeSamplingDetail($samplingid){
        $sampleDetails = Sampling::with(['dealer','user','sampleitems','sale_invoices'])->where('id',$samplingid)->first();
        $sampleDetails = json_decode(json_encode($sampleDetails),true);
        //echo "<pre>"; print_r($sampleDetails); die;
        $linkedProducts = \App\DealerLinkedProduct::where('dealer_id',$sampleDetails['dealer_id'])->pluck('product_id')->toArray();
        $title = "Free Sampling Detail";
        return view('admin.samplings.free-sampling-detail')->with(compact('sampleDetails','title','linkedProducts'));
    }

    public function paidSamplingDetail($samplingid){
        $sampleDetails = Sampling::with(['dealer','user','sampleitems','sale_invoices'])->where('id',$samplingid)->first();
        $sampleDetails = json_decode(json_encode($sampleDetails),true);
        //echo "<pre>"; print_r($sampleDetails); die;
        $linkedProducts = \App\DealerLinkedProduct::where('dealer_id',$sampleDetails['dealer_id'])->pluck('product_id')->toArray();
        $title = "Paid Sampling Detail";
        return view('admin.samplings.paid-sampling-detail')->with(compact('sampleDetails','title','linkedProducts'));
    }

    public function markUrgentSampleItem(Request $request){
        if($request->ajax()){
            $data = $request->all();
            SamplingItem::where('id',$data['orderitemid'])->update(['item_action'=>$data['value']]);
            return response()->json(['status'=>true]);
        }
    }

    public function UpdateSamplingStatus(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            Sampling::where('id',$data['sampling_id'])->update(['sample_status'=>$data['sample_status'],'comments'=>$data['comments'],'reason'=> $data['reason']]);
            return redirect()->back()->with('flash_message_success','Status has been updated successfully');
        }
    }


    // we are not using below function anymore now

    public function updateSamplingQty_DEPRECETAED(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $sampleDetails = Sampling::with(['dealer','user','sampleitems'])->where('id',$data['sampling_id'])->first();
            $sampleDetails = json_decode(json_encode($sampleDetails),true);
            
            $subtotal = 0;
            if($sampleDetails['sample_type'] =='free'){
                //nothing to do here in case of free samplings
            }else{
                foreach($data['item_ids'] as $ikey=> $itemid){
                    $itemDetails = SamplingItem::find($itemid);
                    if($data['actual_qtys'][$ikey]>$itemDetails['qty']){
                        return redirect()->back()->with('flash_message_error','You have entered wrong qty');
                    }
                    /*if($data['actual_pack_sizes'][$ikey]>$itemDetails['pack_size']){
                        return redirect()->back()->with('flash_message_error','You have entered wrong pack size');
                    }*/
                    if(!empty($sampleDetails['dealer'])){
                        if($data['product_links'][$ikey] == 0){
                            if($data['actual_qtys'][$ikey] > 0){
                                return redirect()->back()->with('flash_message_error','You can not accept an order from non linked product');
                            }
                        }
                    }
                }
            }
            //echo "<pre>"; print_r($data); die;
            //DB::beginTransaction();
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = SamplingItem::find($itemid);
                $itemDetails->actual_qty = $data['actual_qtys'][$ikey];
                $itemDetails->actual_pack_size = $data['actual_pack_sizes'][$ikey];
                $itemDetails->comments = $data['comments'][$ikey];
                $itemDetails->dispatched_qty = 0;
                $itemDetails->save();
                $subtotal +=  $itemDetails->net_price * $data['actual_qtys'][$ikey];
                if($sampleDetails['sample_type'] =='paid'){
                    //Create or update dealer Pending orders
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$sampleDetails['dealer_id'],'product_id'=>$itemDetails->product_id])->first();
                    if($dealerProd){
                        $dealerProduct =  DealerProduct::find($dealerProd->id);
                        $pendingOrders = $dealerProd->pending_orders;
                    }else{
                        $dealerProduct = new DealerProduct;
                        $dealerProduct->dealer_id = $sampleDetails['dealer_id'];
                        $dealerProduct->product_id = $itemDetails->product_id;
                        $pendingOrders = 0;
                    }
                    $dealerProduct->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                    $dealerProduct->save();
                }else{
                    $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$itemDetails->product_id]);
                    if(!empty($sampleDetails['dealer'])){
                        $freeSamplingStock = $freeSamplingStock->where('dealer_id',$sampleDetails['dealer_id']);
                    }else{
                        $freeSamplingStock = $freeSamplingStock->where('user_id',$sampleDetails['user_id']);
                    }
                    $freeSamplingStock = $freeSamplingStock->first();
                    if($freeSamplingStock){
                        $sampleProd =  FreeSamplingStock::find($freeSamplingStock->id);
                        $pendingOrders = $freeSamplingStock->pending_orders;
                    }else{
                        $sampleProd = new FreeSamplingStock;
                        $sampleProd->dealer_id = $sampleDetails['dealer_id'];
                        $sampleProd->user_id = $sampleDetails['user_id'];
                        $sampleProd->customer_id = $sampleDetails['customer_id'];
                        $sampleProd->product_id = $itemDetails->product_id;
                        $pendingOrders = 0;
                    }
                    $sampleProd->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                    $sampleProd->save();
                }
            }
            $updateSampling = Sampling::find($data['sampling_id']);
            $updateSampling->subtotal = $subtotal;
            $gstVal = (($subtotal * $updateSampling->gst_per)/100);
            $updateSampling->gst = $gstVal;
            $updateSampling->grand_total = $subtotal + $gstVal;
            $updateSampling->sample_edited = 'yes';
            $updateSampling->sample_status = 'approved';
            if(isset($data['required_through'])){
                $updateSampling->required_through = $data['required_through'];
            }
            $updateSampling->save();
            //DB::commit();
            if(isset($data['source'])){
                return redirect::to('/admin/paid-sampling')->with('flash_message_success','Qty has been updates successfully');
            }else{
                return redirect::to('/admin/free-sampling')->with('flash_message_success','Qty has been updates successfully');
            }
        }
    }

    public function updateSamplingQty(Request $request)
    {
        if (!$request->isMethod('post')) {
            return redirect()->back();
        }

        DB::beginTransaction();

        try {

            $data = $request->all();

            $sampleDetails = Sampling::with(['dealer','user','sampleitems'])
                ->where('id', $data['sampling_id'])
                ->first();

            if (!$sampleDetails || $sampleDetails->sample_type !== 'free') {
                return redirect()->back()->with('flash_message_error', 'Invalid sampling request');
            }

            $subtotal = 0;

            /* ----------------------------
             | VALIDATION FOR FREE SAMPLE
             |---------------------------- */
            foreach ($data['item_ids'] as $ikey => $itemid) {

                $itemDetails = SamplingItem::find($itemid);

                if (!$itemDetails) {
                    return redirect()->back()->with('flash_message_error', 'Invalid item');
                }

                /*
                if ($data['actual_qtys'][$ikey] > $itemDetails->qty) {
                    return redirect()->back()->with('flash_message_error', 'You have entered wrong qty');
                }
                */
            }

            /* ----------------------------
             | UPDATE ITEMS + FREE STOCK
             |---------------------------- */
            foreach ($data['item_ids'] as $ikey => $itemid) {

                $itemDetails = SamplingItem::find($itemid);

                $itemDetails->product_id            = $data['product_ids'][$ikey];
                $itemDetails->actual_qty            = $data['actual_qtys'][$ikey];
                $itemDetails->actual_pack_size      = $data['actual_pack_sizes'][$ikey];
                $itemDetails->actual_no_of_packs    = $data['actual_no_of_packs'][$ikey];
                $itemDetails->comments              = $data['comments'][$ikey];
                $itemDetails->dispatched_qty        = 0;
                $itemDetails->required_through = $data['required_through'][$ikey];
                $itemDetails->dealer_price = $data['dealer_prices'][$ikey];
                $itemDetails->final_value = $itemDetails->dealer_price * $itemDetails->actual_qty;
                $itemDetails->save();

                $subtotal += $itemDetails->net_price * $data['actual_qtys'][$ikey];

                /* FREE SAMPLING STOCK UPDATE */
                $freeStockQuery = DB::table('free_sampling_stocks')
                    ->where('product_id', $itemDetails->product_id)
                    ->where('user_id', $sampleDetails->user_id);

                $freeSamplingStock = $freeStockQuery->first();

                if ($freeSamplingStock) {
                    $sampleProd = FreeSamplingStock::find($freeSamplingStock->id);
                    $pendingOrders = $freeSamplingStock->pending_orders;
                } else {
                    $sampleProd = new FreeSamplingStock;
                    $sampleProd->user_id     = $sampleDetails->user_id;
                    $sampleProd->customer_id = $sampleDetails->customer_id;
                    $sampleProd->product_id  = $itemDetails->product_id;
                    $pendingOrders = 0;
                }

                $sampleProd->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                $sampleProd->save();
            }

            /* ----------------------------
             | UPDATE SAMPLING MASTER
             |---------------------------- */
            $updateSampling = Sampling::find($data['sampling_id']);
            $updateSampling->subtotal       = $subtotal;
            $updateSampling->gst            = 0;
            $updateSampling->grand_total    = 0;
            $updateSampling->sample_edited  = 'yes';
            $updateSampling->sample_status  = 'approved';

            /*if (isset($data['required_through'])) {
                $updateSampling->required_through = $data['required_through'];
            }*/

            $updateSampling->save();

            DB::commit();

            return redirect('/admin/free-sampling')
                ->with('flash_message_success', 'Free sample qty updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Free Sampling Update Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(), // optional but very useful
                'request' => $request->all(),
            ]);


            return redirect()->back()
                ->with('flash_message_error', 'Something went wrong. Please try again.');
        }
    }


    public function addSamplingItem(Request $request)
    {
        $request->validate([
            'sampling_id' => 'required|exists:samplings,id',
            'product_id'  => 'required|exists:products,id',
            'pack_size'   => 'required|numeric',
            'no_of_packs' => 'required|numeric|min:1',
        ]);

        $sampling = Sampling::findOrFail($request->sampling_id);

        if ($sampling->sample_edited === 'yes') {
            return redirect()->back()
                ->with('flash_message_error', 'This sample is already approved.');
        }

        DB::beginTransaction();

        try {

            SamplingItem::create([
                'sampling_id'   => $sampling->id,
                'requested_product_id'    => $request->product_id,
                'product_id'    => $request->product_id,
                'pack_size'     => $request->pack_size,
                'actual_pack_size'     => $request->pack_size,
                'no_of_packs'   => $request->no_of_packs,
                'actual_no_of_packs'   => $request->no_of_packs,
                'qty'           => $request->pack_size * $request->no_of_packs,
                'actual_qty'           => $request->pack_size * $request->no_of_packs,
                'dispatched_qty'=> 0,
                'requested_from' => 'admin'
            ]);

            DB::commit();

            return redirect()->back()
                ->with('flash_message_success', 'Product added successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Add Sampling Item Failed', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('flash_message_error', 'Unable to add product');
        }
    }

    public function deleteSamplingItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:sampling_items,id',
        ]);

        $item = SamplingItem::findOrFail($request->item_id);
        $sampling = Sampling::findOrFail($item->sampling_id);

        // Block delete if already approved
        if ($sampling->sample_edited === 'yes') {
            return redirect()->back()
                ->with('flash_message_error', 'Approved samples cannot be modified.');
        }

        // Allow delete ONLY if added by admin
        if ($item->requested_from !== 'admin') {
            return redirect()->back()
                ->with('flash_message_error', 'You cannot delete this item.');
        }

        DB::beginTransaction();
        try {

            $item->delete();

            DB::commit();

            return redirect()->back()
                ->with('flash_message_success', 'Product deleted successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Delete Sampling Item Failed', [
                'msg' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('flash_message_error', 'Unable to delete product');
        }
    }



     public function samplingDispatchPlanning(Request $Request){
        Session::put('active','samplingDispatchPlanning'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = SamplingItem::with('sampling')->join('products','products.id','=','sampling_items.product_id')->join('samplings','samplings.id','=','sampling_items.sampling_id')->leftjoin('dealers','dealers.id','=','samplings.dealer_id')->leftjoin('users','users.id','=','samplings.user_id')->leftjoin('customers','customers.id','=','samplings.customer_id')->select('samplings.id','sampling_items.id as order_item_id','samplings.created_at','samplings.dealer_id','dealers.business_name','samplings.sample_type','sampling_items.required_through','sampling_items.sampling_id','sampling_items.product_id','sampling_items.actual_qty','sampling_items.dispatched_qty','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','sampling_items.is_urgent','sampling_items.item_action','customers.name as customer_name','users.name as executive_name','users.mobile as executive_mobile','sampling_items.actual_pack_size')->where('samplings.sample_status','approved')->whereColumn('sampling_items.actual_qty','!=','sampling_items.dispatched_qty');
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['customer_info'])){
                $keyword = $data['customer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('customers.name', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['user_type'])){
                if($data['user_type'] =="Dealer"){
                    $querys = $querys->where('dealers.business_name','!=','');
                }else{
                    $querys = $querys->where('users.name','!=','');
                }   
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%' .$data['product_name']. '%');
            }
            if(!empty($data['po_no'])){
                $querys = $querys->where('samplings.sample_ref_no_string',$data['po_no']);
            }
            if(!empty($data['urgent'])){
                $querys = $querys->where('sampling_items.item_action',$data['urgent']);
            }
            if(!empty($data['date'])){
                $querys = $querys->whereDate('sampling_items.created_at',$data['date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('sampling_items.created_at','ASC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $poInfo){ 
                $userInfo = "";
                if(!empty($poInfo['business_name'])){
                    $userInfo = ucwords($poInfo['business_name']);
                }else{
                    $userInfo = ucwords($poInfo['executive_name']);
                }
                $actionValues = "";
                if($poInfo['item_action'] =="" || $poInfo['item_action'] =="Urgent"){
                    $actionValues='<a style="display:none;" title="Update Status" class="btn btn-sm green margin-top-10 getProductBatches" data-orderitemid="'.$poInfo['order_item_id'].'" href="javascript:;"> Update</a>
                    <a title="Update Status" class="btn btn-sm green margin-top-10 openDispatchItemModal" data-username="'.$userInfo.'" data-productname="'.$poInfo['product_name'].'" data-orderitemid="'.$poInfo['order_item_id'].'" href="javascript:;"> Update</a>';
                }
                
                $item_action = "";
                if($poInfo['item_action'] != ''){
                    if($poInfo['item_action']=="On Hold"){
                        $item_action = '<span class="badge badge-warning">'.$poInfo['item_action'].'</span>';
                    }else if($poInfo['item_action']=="Cancel"){
                        $item_action = '<span class="badge badge-dark">'.$poInfo['item_action'].'</span>';
                    }else if($poInfo['item_action']=="Urgent"){
                        $item_action = '<span class="badge badge-danger">'.$poInfo['item_action'].'</span>';
                    }
                }
                $type = "Executive";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $records["data"][] = array(
                    $poInfo['sampling']['sample_ref_no_string'].'<br><small>'.
                    date('d M Y',strtotime($poInfo['created_at'])).'<small>', 
                    $userInfo,
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>'.$item_action,
                    $poInfo['actual_qty'] - $poInfo['dispatched_qty'].'kg <br><small>('.$poInfo['actual_pack_size'].'kg Packing)</small>',
                    ucwords($poInfo['required_through']),
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Sample Dispatch Planning";
        return View::make('admin.samplings.dispatch-planning')->with(compact('title'));
    }


    public function updateProSampleDispatchQty(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $orderitemDetails = SamplingItem::details($data['order_item_id']);
            $requiredQty = $orderitemDetails['actual_qty'] - $orderitemDetails['dispatched_qty'];
            $stocks = $data['issue_stock'];
            $requestedStock = $data['issue_stock'];
            if(!is_numeric($requestedStock)){
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Please enter the issue stock')]);
            }
            if($requestedStock > $requiredQty){
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Your requested issue qty must be less then or equal to '.$requiredQty)]);
            }else{
                $poinfo = Sampling::where('id',$orderitemDetails['sampling_id'])->first();
                DB::beginTransaction();
                //Create Sale Invoice
                $createSO = new SamplingSaleInvoice;
                //$createSO->sale_invoice_date = date('Y-m-d');
                $createSO->sampling_id =  $orderitemDetails['sampling_id'];
                $createSO->sampling_item_id =  $data['order_item_id'];
                $createSO->dealer_id =  $poinfo->dealer_id;
                $createSO->user_id =  $poinfo->user_id;
                $createSO->customer_id =  $poinfo->customer_id;
                $createSO->batch_no = $data['batch_no'];
                $createSO->product_id = $orderitemDetails['product_id'];
                $createSO->qty = $requestedStock;
                $createSO->price = $orderitemDetails['net_price'];
                $createSO->subtotal = $orderitemDetails['net_price'] * $requestedStock;
                
                $totalPrice = $orderitemDetails['net_price'] * $requestedStock;
                $totatSaleAmt = $orderitemDetails['net_price'] * $requestedStock;
                //Update sale invoice 
                $data['gst'] = 18;
                $calGST =  ($totatSaleAmt *$data['gst']) /100;
                $totatSaleAmt = $totatSaleAmt + $calGST;
                $createSO->price =  $totalPrice;
                $createSO->gst   = $calGST;
                $createSO->gst_per   = $data['gst'];
                $createSO->grand_total = $totatSaleAmt;
                $createSO->save();
                //Update PO
                SamplingItem::where('id',$orderitemDetails['id'])->increment('dispatched_qty',$requestedStock);
                //Decrement Stock
                Product::where('id',$orderitemDetails['product_id'])->decrement('current_stock',$requestedStock);
                $getAllPO= SamplingItem::where('sampling_id',$orderitemDetails['sampling_id'])->whereColumn('actual_qty','!=','dispatched_qty')->count();
                if($getAllPO == 0){
                    Sampling::where('id',$orderitemDetails['sampling_id'])->update(['sample_status'=>'executed']);
                }
                DB::commit();
                return response()->json([
                    'status' =>true,
                    'url'  => url('admin/sampling-dispatch-planning')
                ]);
            }
        }
    }

    public function sampleFinalizeDo()
    {
        Session::put('active', 'sampleFinalizeDo');

        $title = "Finalize D.O.";
        $type  = 'free';

        $requiredThroughs = ['courier', 'transport'];

        /* -----------------------------
         | 1. Get Executives
         |-----------------------------*/
        $users = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')
            ->join('users','users.id','=','sampling_sale_invoices.user_id')
            ->where('samplings.sample_type','free')
            ->where('sampling_sale_invoices.do_number','')
            ->where('sampling_sale_invoices.invoice_no','')
            ->whereNotNull('sampling_sale_invoices.user_id')
            ->groupBy('sampling_sale_invoices.user_id')
            ->select('sampling_sale_invoices.user_id','users.name')
            ->orderBy('users.name')
            ->get()
            ->toArray();

        $userIds = array_column($users, 'user_id');

        /* -----------------------------
         | 2. Get ALL invoices at once
         |-----------------------------*/
        $allInvoices = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')
            ->join('products','products.id','=','sampling_sale_invoices.product_id')
            ->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')
            ->where('samplings.sample_type','free')
            ->where('sampling_sale_invoices.do_number','')
            ->where('sampling_sale_invoices.invoice_no','')
            ->whereIn('sampling_sale_invoices.user_id', $userIds)
            ->select(
                'sampling_sale_invoices.id as sale_invoice_id',
                'sampling_sale_invoices.sampling_item_id',
                'sampling_sale_invoices.user_id',
                'sampling_items.required_through',
                'products.product_name',
                'products.product_code',
                'sampling_items.actual_pack_size',
                'sampling_items.comments',
                'sampling_sale_invoices.qty'
            )
            ->get()
            ->toArray();

        /* -----------------------------
         | 3. Index invoices (user + through)
         |-----------------------------*/
        $invoiceMap = [];

        foreach ($allInvoices as $row) {
            $invoiceMap[$row['user_id']][$row['required_through']][] = $row;
        }

        /* -----------------------------
         | 4. Attach invoices to users
         |-----------------------------*/
        foreach ($users as &$user) {

            foreach ($requiredThroughs as $required) {

                $rows = $invoiceMap[$user['user_id']][$required] ?? [];

                $user['invoices'][$required] = [
                    'sale_invoices'    => $rows,
                    'sale_invoice_ids' => implode(',', array_column($rows, 'sale_invoice_id'))
                ];
            }
        }

        return view(
            'admin.samplings.finalize-do',
            compact('title','users','type','requiredThroughs')
        );
    }

    public function undoSampleFinalizeDO($saleInvoiceid,$samplingitemid){
        $SamplingSaleInvoice = SamplingSaleInvoice::where('id',$saleInvoiceid)->first();
        $details = SamplingItem::find($samplingitemid);
        SamplingItem::where('id',$samplingitemid)->decrement('dispatched_qty',$SamplingSaleInvoice->qty);
        Product::where('id',$details->product_id)->increment('current_stock',$SamplingSaleInvoice->qty);
        SamplingSaleInvoice::where('id',$saleInvoiceid)->delete();
        return redirect::to('/admin/sample-finalize-do')->with('flash_message_success','Updated successfully');
    }

    public function samplingGenerateDoNumbers(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
            $lastDoNumber = SamplingSaleInvoice::orderby('do_number','DESC')->where('do_number','>',0)->where('do_financial_year',financialYear())->first();
            if(is_object($lastDoNumber)){
                $doNumber = $lastDoNumber->do_number +1;
            }else{
                $doNumber = 1;
            }
            foreach($saleInvoiceIds as $invoiceid){
                $saleInvoice = SamplingSaleInvoice::find($invoiceid);
                $saleInvoice->do_number = $doNumber;
                $saleInvoice->do_ref_no = "S-".$doNumber.'/'.financialYear();
                $saleInvoice->do_financial_year = financialYear();
                $saleInvoice->do_date = date('Y-m-d H:i:s');
                $saleInvoice->save();
            }
            Session::flash('flash_message_success','DO number has been generated successfully');
            return response()->json([
                'status' =>true,
                'url'    =>  url('/admin/sample-finalize-do/')
            ]);
        }
    }

    public function sampleDoReady()
    {
        Session::put('active', 'sampleDoReady');

        $title = "D.O. Ready";

        /* ---------------------------------
         | 1. Get D.O. headers (executive only, free only)
         |---------------------------------*/
        $dos = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')
            ->join('users','users.id','=','sampling_sale_invoices.user_id')
            ->where('samplings.sample_type','free')
            ->where('sampling_sale_invoices.invoice_no','')
            ->where('sampling_sale_invoices.do_number','!=','')
            ->whereNotNull('sampling_sale_invoices.user_id')
            ->groupBy(
                'sampling_sale_invoices.do_ref_no'
            )
            ->select(
                'sampling_sale_invoices.do_ref_no',
                'sampling_sale_invoices.do_date',
                'sampling_sale_invoices.user_id',
                'users.name',
                'samplings.required_through',
                'samplings.sample_type'
            )
            ->orderBy('sampling_sale_invoices.do_ref_no')
            ->get()
            ->toArray();

        $doNumbers = array_column($dos, 'do_ref_no');

        /* ---------------------------------
         | 2. Get ALL D.O. invoices at once
         |---------------------------------*/
        $allInvoices = SamplingSaleInvoice::join('products','products.id','=','sampling_sale_invoices.product_id')
            ->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')
            ->whereIn('sampling_sale_invoices.do_ref_no', $doNumbers)
            ->where('sampling_sale_invoices.invoice_no','')
            ->select(
                'sampling_sale_invoices.id as sale_invoice_id',
                'sampling_sale_invoices.do_ref_no',
                'products.product_name',
                'sampling_sale_invoices.qty',
                'sampling_items.required_through'
            )
            ->get()
            ->toArray();

        /* ---------------------------------
         | 3. Group invoices by DO number
         |---------------------------------*/
        $invoiceMap = [];

        foreach ($allInvoices as $row) {
            $invoiceMap[$row['do_ref_no']][] = $row;
        }

        /* ---------------------------------
         | 4. Attach invoices to DOs
         |---------------------------------*/
        foreach ($dos as &$do) {

            $rows = $invoiceMap[$do['do_ref_no']] ?? [];

            $do['invoices'] = [
                'sale_invoices'    => $rows,
                'sale_invoice_ids' => implode(',', array_column($rows,'sale_invoice_id'))
            ];
        }

        return view(
            'admin.samplings.sample-do-ready',
            compact('title','dos')
        );
    }


    public function updateBulkSampleSaleInvoice(Request $request){
        if($request->all()){
            $data = $request->all();
            $invoice_no_details = SamplingSaleInvoice::where('invoice_no',$data['invoice_number'])->first();
            if(is_object($invoice_no_details)){
                $invoice_no_details = json_decode(json_encode($invoice_no_details),true);
                if(!empty($data['dealer_id']) &&  !empty($invoice_no_details['dealer_id'])){
                    if($invoice_no_details['dealer_id'] != $data['dealer_id']){
                        return redirect::to('admin/do-ready')->with('flash_message_error','This Invoice no has already been used.'); 
                    }
                }elseif(!empty($data['user_id']) &&  !empty($lr_no_detials['user_id'])){
                    if($lr_no_detials['user_id'] != $data['user_id']){
                        return redirect::to('admin/do-ready')->with('flash_message_error','This Invoice no has already been used.'); 
                    }
                }
            }
            $data = $request->all();
            $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
            foreach($saleInvoiceIds as $saleInvoice){
                $saleInv = SamplingSaleInvoice::find($saleInvoice);
                $saleInv->sale_invoice_date = $data['sale_invoice_date'];
                $saleInv->invoice_no = $data['invoice_number'];
                $saleInv->save();
            }
            Session::flash('flash_message_success','Details has been updated successfully');
            return redirect::to('/admin/sample-do-ready');
        }
    }


    public function sampleBillReady()
    {
        Session::put('active', 'sampleBillReady');

        $title = "Bill Ready";

        /* ---------------------------------
         | 1. Get Invoice headers
         |---------------------------------*/
        $invoices = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')
            ->join('users','users.id','=','sampling_sale_invoices.user_id')
            ->where('samplings.sample_type','free')
            ->where('sampling_sale_invoices.invoice_no','!=','')
            ->where('sampling_sale_invoices.transport_name','')
            ->whereNotNull('sampling_sale_invoices.user_id')
            ->groupBy(
                'sampling_sale_invoices.invoice_no'
            )
            ->select(
                'sampling_sale_invoices.invoice_no',
                'sampling_sale_invoices.sale_invoice_date',
                'sampling_sale_invoices.user_id',
                'users.name',
                'samplings.required_through',
                'samplings.sample_type'
            )
            ->orderBy('sampling_sale_invoices.sale_invoice_date')
            ->get()
            ->toArray();

        $invoiceNumbers = array_column($invoices, 'invoice_no');

        /* ---------------------------------
         | 2. Get ALL invoice items at once
         |---------------------------------*/
        $allItems = SamplingSaleInvoice::join('products','products.id','=','sampling_sale_invoices.product_id')
            ->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')
            ->whereIn('sampling_sale_invoices.invoice_no', $invoiceNumbers)
            ->where('sampling_sale_invoices.transport_name','')
            ->select(
                'sampling_sale_invoices.id as sale_invoice_id',
                'sampling_sale_invoices.invoice_no',
                'products.product_name',
                'sampling_sale_invoices.qty',
                'sampling_items.required_through'
            )
            ->get()
            ->toArray();

        /* ---------------------------------
         | 3. Group items by invoice no
         |---------------------------------*/
        $itemMap = [];

        foreach ($allItems as $row) {
            $itemMap[$row['invoice_no']][] = $row;
        }

        /* ---------------------------------
         | 4. Attach items to invoices
         |---------------------------------*/
        foreach ($invoices as &$invoice) {

            $rows = $itemMap[$invoice['invoice_no']] ?? [];

            $invoice['items'] = [
                'sale_invoices'    => $rows,
                'sale_invoice_ids' => implode(',', array_column($rows,'sale_invoice_id'))
            ];
        }

        return view(
            'admin.samplings.sample-bill-ready',
            compact('title','invoices')
        );
    }

    public function updateBulkSampleLrSaleInvoice(Request $request){
        if($request->all()){
            $data = $request->all();
            $lr_no_detials = SamplingSaleInvoice::where('lr_no',$data['lr_no'])->first();
            if(is_object($lr_no_detials)){
                $lr_no_detials = json_decode(json_encode($lr_no_detials),true);
                if(!empty($data['dealer_id']) &&  !empty($lr_no_detials['dealer_id'])){
                    if($lr_no_detials['dealer_id'] != $data['dealer_id']){
                        return redirect::to('admin/sample-bill-ready')->with('flash_message_error','This LR no has already been used.'); 
                    }
                }elseif(!empty($data['user_id']) &&  !empty($lr_no_detials['user_id'])){
                    if($lr_no_detials['user_id'] != $data['user_id']){
                        return redirect::to('admin/sample-bill-ready')->with('flash_message_error','This LR no has already been used.'); 
                    }
                }
            }
            $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
            foreach($saleInvoiceIds as $saleInvoice){
                $saleInv = SamplingSaleInvoice::with('sampling')->find($saleInvoice);
                $saleInv->dispatch_date = $data['dispatch_date'];
                $saleInv->lr_no = $data['lr_no'];
                $saleInv->transport_name = $data['transport_name'];
                $saleInv->save();
                //Manage Stock here
                if($saleInv->sampling->sample_type == "free")
                {
                    if(!empty($saleInv->user_id)){
                        //for executive
                        $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$saleInv->product_id,'user_id'=>$saleInv->user_id])->first();
                    }else if(!empty($saleInv->dealer_id)){
                        //for executive
                        $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$saleInv->product_id,'dealer_id'=>$saleInv->dealer_id])->first();
                    }
                    $freesampleProd =  FreeSamplingStock::find($freeSamplingStock->id);
                    $pendingOrders = $freesampleProd->pending_orders;
                    $freesampleProd->pending_orders = $pendingOrders- $saleInv->qty;
                    $freesampleProd->in_transit = $freesampleProd->in_transit + $saleInv->qty;
                    $freesampleProd->save();
                
                }else{
                    //for paid samplings
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$saleInv->dealer_id,'product_id'=>$saleInv->product_id])->first();
                    if(is_object($dealerProd)) {
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $pendingOrders = $updatedealerProd->pending_orders;
                        $updatedealerProd->pending_orders = $pendingOrders- $saleInv->qty;
                        $updatedealerProd->in_transit = $updatedealerProd->in_transit + $saleInv->qty;
                        $updatedealerProd->save();
                    }
                }
            }
            Session::flash('flash_message_success','Details has been updated successfully');
            return redirect::to('/admin/sample-bill-ready');
        }
    }


    public function sampleDispatchedMaterial(Request $request)
    {
        $data = $request->all();

        Session::put('active','sampleDispatchedMaterial');
        $title = "Dispatched Sample";

        /* ---------------------------------
         | 1. Base invoice query (FREE + EXECUTIVE)
         |---------------------------------*/
        $query = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')
            ->join('users','users.id','=','sampling_sale_invoices.user_id')
            ->where('samplings.sample_type','free')
            ->where('sampling_sale_invoices.invoice_no','!=','')
            ->where('sampling_sale_invoices.lr_no','!=','')
            ->whereNotNull('sampling_sale_invoices.user_id')
            ->select(
                'sampling_sale_invoices.invoice_no',
                'sampling_sale_invoices.sale_invoice_date',
                'sampling_sale_invoices.dispatch_date',
                'sampling_sale_invoices.lr_no',
                'sampling_sale_invoices.user_id',
                'users.name',
                'samplings.sample_ref_no_string',
                'samplings.required_through',
                'samplings.sample_type'
            )
            ->orderBy('sampling_sale_invoices.dispatch_date','DESC')
            ->groupBy(
                'sampling_sale_invoices.invoice_no'
            );

        /* ---------------------------------
         | 2. Filters
         |---------------------------------*/
        if (!empty($data['name'])) {
            $query->where('users.name','like','%'.$data['name'].'%');
        }

        if (!empty($data['product_id'])) {
            $query->where('sampling_sale_invoices.product_id',$data['product_id']);
        }

        if (!empty($data['batch_no'])) {
            $query->where('sampling_sale_invoices.batch_no',$data['batch_no']);
        }

        $users = $query->simplePaginate(500);

        /* ---------------------------------
         | 3. Fetch ALL dispatched items (single query)
         |---------------------------------*/
        $invoiceNos = collect($users->items())->pluck('invoice_no')->toArray();

        $items = SamplingSaleInvoice::join('products','products.id','=','sampling_sale_invoices.product_id')
            ->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')
            ->whereIn('sampling_sale_invoices.invoice_no', $invoiceNos)
            ->where('sampling_sale_invoices.lr_no','!=','')
            ->select(
                'sampling_sale_invoices.invoice_no',
                'products.product_name',
                'sampling_sale_invoices.qty',
                'sampling_sale_invoices.batch_no',
                'sampling_sale_invoices.price',
                'sampling_items.required_through'
            )
            ->get()
            ->toArray();

        /* ---------------------------------
         | 4. Group items by invoice no
         |---------------------------------*/
        $itemMap = [];
        foreach ($items as $row) {
            $itemMap[$row['invoice_no']][] = $row;
        }

        /* ---------------------------------
         | 5. Attach items to paginated users
         |---------------------------------*/
        foreach ($users as &$user) {
            $user->items = $itemMap[$user->invoice_no] ?? [];
        }

        return view(
            'admin.samplings.sample-dispatched-material',
            compact('title','users','data')
        );
    }

    //deperecated
    public function sampleDispatchedMaterial_not_using(Request $request){
        $data = $request->all();
        Session::put('active','sampleDispatchedMaterial'); 
        $title = "Dispatched Sample";
        $users = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->leftjoin('dealers','dealers.id','=','sampling_sale_invoices.dealer_id')->leftjoin('users','users.id','=','sampling_sale_invoices.user_id')->where('sampling_sale_invoices.invoice_no','!=','')->where('sampling_sale_invoices.lr_no','!=','')->select('sampling_sale_invoices.dealer_id','sampling_sale_invoices.dispatch_date','sampling_sale_invoices.sale_invoice_date','sampling_sale_invoices.user_id','sampling_sale_invoices.invoice_no','sampling_sale_invoices.lr_no','sampling_sale_invoices.batch_no','samplings.sample_ref_no_string','samplings.required_through','samplings.sample_type','dealers.business_name as dealer_name','users.name as executive_name',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(users.name,'')) AS name"))->orderby('dispatch_date','DESC');
            if(isset($data['product_id'])&& !empty($data['product_id'])){
                $users = $users->where('sampling_sale_invoices.product_id',$data['product_id']);
            }
            if(isset($data['batch_no'])&& !empty($data['batch_no'])){
                $users = $users->where('sampling_sale_invoices.batch_no',$data['batch_no']);
            }
            if(isset($data['name'])){
                $users = $users->where(function($query)use($data){
                    $query->where('users.name','like', '%' .$data['name']. '%')->where('sampling_sale_invoices.dispatch_date','!=','0000-00-00');
                })->orwhere(function($query)use($data){
                    $query->where('dealers.business_name','like', '%' .$data['name']. '%')->where('sampling_sale_invoices.dispatch_date','!=','0000-00-00');
                });
            }
            $users = $users->groupby('sampling_sale_invoices.invoice_no')->simplePaginate(500);
        //echo "<pre>"; print_r(json_decode(json_encode($users),true)); die;
        return view('admin.samplings.sample-dispatched-material')->with(compact('title','users','data'));
    }

    public function downloadPdf($id)
    {
        $sampling = Sampling::with([
            'sampleitems',
            'customer',
            'user'      // executive
        ])->findOrFail($id);

        $pdf = PDF::loadView('admin.samplings.free.pdf', compact('sampling'))
            ->setPaper('A4', 'portrait');

        return $pdf->download(
            'Sampling-' . $sampling->sample_ref_no_string . '.pdf'
        );
    }
}
