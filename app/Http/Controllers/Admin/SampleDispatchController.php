<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Dealer;
use App\Http\Requests\Admin\StoreSamplingDispatchRequest;
use App\Product;
use App\SamplingDispatch;
use App\SamplingRequest;
use App\Services\SamplingDispatchService;
use App\User;
use Illuminate\Http\Request;
use Session;

/**
 * Step 2 of the revamped sampling flow.
 *
 * One page replaces the old four (dispatch planning → finalize DO → DO ready →
 * bill ready): pick any number of approved-and-ready request lines, enter the
 * qty, batch and where each one goes, then one common challan and transport
 * block for the whole lot.
 *
 *   index()         GET  /admin/sample-dispatch
 *   create()        GET  /admin/sample-dispatch/create
 *   store()         POST /admin/sample-dispatch                      [AJAX]
 *   readyRequests() GET  /admin/sample-dispatch/requests             [AJAX]
 */
class SampleDispatchController extends Controller
{
    /** @var SamplingDispatchService */
    private $service;

    public function __construct(SamplingDispatchService $service)
    {
        $this->service = $service;
    }

    // ══════════════════════════════════════════════════════════════════
    //  LISTING
    // ══════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        Session::put('active', 'samplingDispatch');

        $query = SamplingDispatch::with([
                'sampling_request:id,reference_no,user_id,dealer_id,is_voluntarily',
                'sampling_request.user:id,name',
                'sampling_request.dealer:id,business_name,name',
                'product:id,product_name,product_code',
                'dispatch_dealer:id,business_name,name',
                'dispatch_customer:id,name',
                'dispatch_user:id,name',
            ]);

        if ($request->filled('user_id')) {
            $query->whereHas('sampling_request', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }
        if ($request->filled('dealer_id')) {
            $query->whereHas('sampling_request', function ($q) use ($request) {
                $q->where('dealer_id', $request->dealer_id);
            });
        }
        if ($request->filled('answerable_type')) {
            $query->whereHas('sampling_request', function ($q) use ($request) {
                $request->answerable_type === 'dealer'
                    ? $q->whereNotNull('dealer_id')
                    : $q->whereNull('dealer_id');
            });
        }
        if ($request->filled('dispatch_type')) {
            $query->whereHas('sampling_request', function ($q) use ($request) {
                $q->where('is_voluntarily', $request->dispatch_type === 'voluntary' ? 1 : 0);
            });
        }
        // Receipt is not a request status: a closed request stays closed, and
        // this only says whether the employee has confirmed the material.
        if ($request->filled('receipt')) {
            $request->receipt === 'received' ? $query->received() : $query->notReceived();
        }
        if ($request->filled('from_date')) {
            $query->whereDate('dispatch_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('dispatch_date', '<=', $request->to_date);
        }
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('dispatch_ref_no', 'like', $keyword)
                  ->orWhere('invoice_no', 'like', $keyword)
                  ->orWhere('lr_no', 'like', $keyword)
                  ->orWhere('batch_no', 'like', $keyword)
                  ->orWhereHas('sampling_request', function ($r) use ($keyword) {
                      $r->where('reference_no', 'like', $keyword);
                  });
            });
        }

        $dispatches = $query->orderBy('dispatch_no', 'DESC')
            ->orderBy('id', 'ASC')
            ->paginate(50)
            ->appends($request->except('page'));

        return view('admin.sample_dispatch.index', [
            'title'      => 'Sample Dispatches',
            'dispatches' => $dispatches,
            // Grouped so the table can print one block per challan.
            'grouped'    => $dispatches->getCollection()->groupBy('dispatch_ref_no'),
            'employees'  => User::marketingEmployees()->orderBy('name')->get(['id', 'name']),
            'dealers'    => Dealer::where('status', 1)->whereNull('parent_id')
                ->orderBy('business_name')->get(['id', 'business_name', 'name']),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  DISPATCH FORM
    // ══════════════════════════════════════════════════════════════════

    public function create()
    {
        Session::put('active', 'samplingDispatchCreate');

        return view('admin.sample_dispatch.create', [
            'title'     => 'Sample Dispatch',
            'employees' => User::marketingEmployees()->orderBy('name')->get(['id', 'name']),
            // Head-office dealers only: a branch cannot be answerable for a sample.
            'dealers'   => Dealer::where('status', 1)
                ->whereNull('parent_id')
                ->orderBy('business_name')
                ->get(['id', 'business_name', 'name', 'city', 'owner_mobile', 'address', 'shipping_address']),
            // Needed by the voluntary mode, where the admin picks the product
            // and packing directly instead of loading an approved request.
            'products'  => Product::where('status', 1)
                ->orderBy('product_name')
                ->get(['id', 'product_name', 'product_code', 'physical_form']),
        ]);
    }

    public function store(StoreSamplingDispatchRequest $request)
    {
        $result = $this->service->dispatch($request->validated());

        $message = 'Dispatched ' . $result['lines'] . ' request(s) on ' . $result['dispatch_ref_no'] . '.';
        if ($result['closed']) {
            $message .= ' ' . $result['closed'] . ' request(s) are now closed.';
        }

        Session::flash('flash_message_success', $message);

        return response()->json([
            'status'          => true,
            'dispatch_ref_no' => $result['dispatch_ref_no'],
            'url'             => route('admin.sample-dispatch.index'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  AJAX
    // ══════════════════════════════════════════════════════════════════

    /**
     * Approved lines that still have qty pending.
     *
     * Lines not yet marked ready are returned too — the screen greys them out
     * as "Not ready yet" rather than hiding them, so the dispatch desk can see
     * what is waiting on stores.
     */
    public function readyRequests(Request $request)
    {
        $query = SamplingRequest::with([
                'user:id,name',
                'customer:id,name',
                'product:id,product_name,product_code',
                'dispatch_dealer:id,business_name,name',
                'dispatch_customer:id,name',
                'dispatch_user:id,name',
            ])
            ->pendingDispatch();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('reference_no', 'like', $keyword)
                  ->orWhereHas('product', function ($p) use ($keyword) {
                      $p->where('product_name', 'like', $keyword);
                  })
                  ->orWhereHas('customer', function ($c) use ($keyword) {
                      $c->where('name', 'like', $keyword);
                  });
            });
        }

        $requests = $query->orderBy('is_ready', 'DESC')
            ->orderBy('request_group_no', 'DESC')
            ->orderBy('line_no', 'ASC')
            ->limit(200)
            ->get();

        return response()->json([
            'status'   => true,
            'requests' => $requests->map(function ($r) {
                $partyType = $r->dispatch_dealer_id ? 'dealer' : ($r->dispatch_customer_id ? 'customer' : 'user');

                return [
                    'id'            => $r->id,
                    'reference_no'  => $r->reference_no,
                    'request_date'  => $r->request_date ? $r->request_date->format('d M Y') : null,
                    'employee'      => optional($r->user)->name,
                    'customer'      => optional($r->customer)->name,
                    'product_name'  => optional($r->product)->product_name,
                    'product_code'  => optional($r->product)->product_code,
                    'pack_size'     => $r->pack_size,
                    'requested_qty' => $r->requested_qty,
                    'approved_qty'  => $r->approved_qty,
                    'dispatched'    => $r->total_dispatched_qty,
                    'pending_qty'   => $r->pending_qty,
                    'is_ready'      => (bool) $r->is_ready,
                    'ready_at'      => $r->ready_at ? $r->ready_at->format('d M Y') : null,
                    'ready_remarks' => $r->ready_remarks,
                    'party_type'    => $partyType,
                    'party'         => $this->partyOption($r, $partyType),
                    'address'       => $r->dispatch_address,
                ];
            })->values(),
        ]);
    }

    /** The preselected select2 option for a request's "dispatch to" party. */
    private function partyOption(SamplingRequest $r, $partyType)
    {
        if ($partyType === 'dealer' && $r->dispatch_dealer) {
            return ['id' => $r->dispatch_dealer_id, 'text' => $r->dispatch_dealer->business_name ?: $r->dispatch_dealer->name];
        }
        if ($partyType === 'customer' && $r->dispatch_customer) {
            return ['id' => $r->dispatch_customer_id, 'text' => $r->dispatch_customer->name];
        }
        if ($r->dispatch_user) {
            return ['id' => $r->dispatch_user_id, 'text' => $r->dispatch_user->name];
        }
        if ($r->user) {
            return ['id' => $r->user_id, 'text' => $r->user->name];
        }
        return null;
    }
}
