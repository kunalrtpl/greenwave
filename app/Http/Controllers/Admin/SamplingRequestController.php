<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Dealer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveSamplingRequestRequest;
use App\Http\Requests\Admin\UpdateSamplingRequestRequest;
use App\Product;
use App\SamplingRequest;
use App\SamplingRequestQuery;
use App\Services\SamplingQueryService;
use App\Services\SamplingRequestService;
use App\User;
use Illuminate\Http\Request;
use Session;

/**
 * Step 1 of the revamped sampling flow: everything up to "ready to dispatch".
 *
 * All five status screens are this one listing behind five clean URLs, which
 * is what lets the sidebar list them as separately permissionable modules.
 *
 *   index()      GET  /admin/sampling-requests  (plus /pending /approved
 *                                              /closed /declined)
 *   show()       GET  /admin/sampling-requests/{id}
 *   update()     POST /admin/sampling-requests/{id}                 [AJAX]
 *   approve()    POST /admin/sampling-requests/{id}/approve         [AJAX]
 *   decline()    POST /admin/sampling-requests/{id}/decline         [AJAX]
 *   markReady()  POST /admin/sampling-requests/{id}/mark-ready      [AJAX]
 *   parties()    GET  /admin/sampling-parties?type=dealer|customer  [AJAX]
 */
class SamplingRequestController extends Controller
{
    /** Every request regardless of status. */
    const STATUS_ALL = 'all';

    /** ?status= => the sidebar module that should light up. */
    const STATUS_SESSION_KEYS = [
        self::STATUS_ALL                 => 'samplingRequestsAll',
        SamplingRequest::STATUS_PENDING  => 'samplingRequests',
        SamplingRequest::STATUS_APPROVED => 'samplingRequestsApproved',
        SamplingRequest::STATUS_CLOSED   => 'samplingRequestsClosed',
        SamplingRequest::STATUS_DECLINED => 'samplingRequestsDeclined',
    ];

    const PAGE_TITLES = [
        self::STATUS_ALL                 => 'All Sample Requests',
        SamplingRequest::STATUS_PENDING  => 'Sample Requests',
        SamplingRequest::STATUS_APPROVED => 'Approved — Pending Dispatch',
        SamplingRequest::STATUS_CLOSED   => 'Closed Requests',
        SamplingRequest::STATUS_DECLINED => 'Declined Requests',
    ];

    /** @var SamplingRequestService */
    private $service;

    public function __construct(SamplingRequestService $service)
    {
        $this->service = $service;
    }

    // ══════════════════════════════════════════════════════════════════
    //  LISTING
    // ══════════════════════════════════════════════════════════════════

    public function index(Request $request)    { return $this->listing($request, self::STATUS_ALL); }
    public function pending(Request $request)  { return $this->listing($request, SamplingRequest::STATUS_PENDING); }
    public function approved(Request $request) { return $this->listing($request, SamplingRequest::STATUS_APPROVED); }
    public function closed(Request $request)   { return $this->listing($request, SamplingRequest::STATUS_CLOSED); }
    public function declined(Request $request) { return $this->listing($request, SamplingRequest::STATUS_DECLINED); }

    /** The one listing behind all five URLs. */
    private function listing(Request $request, $status)
    {
        // The sidebar highlight follows the menu entry that was opened, while
        // the status badges on the page may narrow it further.
        Session::put('active', self::STATUS_SESSION_KEYS[$status]);

        // `pageStatus` is the screen that was opened; `status` is what the
        // badges on it narrowed to. They only differ on the combined screen.
        $pageStatus = $status;

        if ($status === self::STATUS_ALL
            && $request->filled('status')
            && isset(self::STATUS_SESSION_KEYS[$request->status])) {
            $status = $request->status;
        }

        // One product is one request: an API post carrying three products is
        // split into three independent requests, each listed on its own row
        // with its own requested / approved / dispatched qty and status.
        $query = SamplingRequest::with([
                'user:id,name',
                'customer:id,name',
                'product:id,product_name,product_code',
                'requested_product:id,product_name,product_code',
            ])
            // A voluntary sample is raised by the admin at dispatch time, not
            // asked for by anyone, so it does not belong in the request lists.
            // It is visible on the dispatch screens instead.
            ->where('is_voluntarily', 0)
            ->when($status !== self::STATUS_ALL, function ($q) use ($status) {
                $q->where('status', $status);
            });

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('product_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('product_id', $request->product_id)
                  ->orWhere('requested_product_id', $request->product_id);
            });
        }
        if ($request->filled('readiness') && $status === SamplingRequest::STATUS_APPROVED) {
            $query->where('is_ready', $request->readiness === 'ready' ? 1 : 0);
        }
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('reference_no', 'like', $keyword)
                  ->orWhereHas('customer', function ($c) use ($keyword) {
                      $c->where('name', 'like', $keyword);
                  })
                  ->orWhereHas('user', function ($u) use ($keyword) {
                      $u->where('name', 'like', $keyword);
                  });
            });
        }

        $requests = $query->orderBy('request_group_no', 'DESC')
            ->orderBy('line_no', 'ASC')
            ->paginate(25)
            ->appends($request->except('page'));

        $queryCounts = app(SamplingQueryService::class)->countsFor($requests->pluck('id')->all());

        $data = [
            'title'       => self::PAGE_TITLES[$pageStatus],
            'status'      => $status,
            'pageStatus'  => $pageStatus,
            'requests'    => $requests,
            'employees'   => User::marketingEmployees()->orderBy('name')->get(['id', 'name']),
            'products'    => Product::where('status', 1)->orderBy('product_name')->get(['id', 'product_name', 'product_code']),
            'counts'      => $this->statusCounts(),
            'queryCounts' => $queryCounts['total'],
            'queryUnread' => $queryCounts['unread'],
        ];

        // Filters apply over AJAX, which asks for the listing on its own.
        return $request->ajax()
            ? view('admin.sampling_requests.partials.table', $data)
            : view('admin.sampling_requests.index', $data);
    }

    /** Clean URL for a status — no query string. */
    public static function statusUrl($status)
    {
        return $status === self::STATUS_ALL
            ? route('admin.sampling-requests.index')
            : route('admin.sampling-requests.' . $status);
    }

    /** Badge numbers beside each status filter. */
    private function statusCounts()
    {
        $rows = SamplingRequest::selectRaw('status, COUNT(*) as total')
            ->where('is_voluntarily', 0)
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [self::STATUS_ALL => (int) $rows->sum()];
        foreach (array_keys(self::STATUS_SESSION_KEYS) as $status) {
            if ($status === self::STATUS_ALL) {
                continue;
            }
            $counts[$status] = (int) $rows->get($status, 0);
        }
        return $counts;
    }

    // ══════════════════════════════════════════════════════════════════
    //  DETAIL
    // ══════════════════════════════════════════════════════════════════

    /**
     * One product of a request (SR-n.m). The listing groups the products of a
     * request together; here the admin works on exactly one, approving or
     * declining it on its own.
     */
    public function show($id)
    {
        $samplingRequest = SamplingRequest::with([
                'user', 'customer', 'dealer',
                'product', 'requested_product',
                'dispatch_dealer', 'dispatch_customer', 'dispatch_user',
                'approved_by:id,name', 'declined_by:id,name', 'ready_by:id,name',
                'dispatches.product:id,product_name,product_code',
                'dispatches.dispatched_by:id,name',
                'logs.user:id,name',
            ])->findOrFail($id);

        Session::put('active', self::STATUS_SESSION_KEYS[$samplingRequest->status]);

        $thread = app(SamplingQueryService::class)
            ->thread($samplingRequest, SamplingRequestQuery::SENDER_ADMIN);

        return view('admin.sampling_requests.show', [
            'title'           => 'Request ' . $samplingRequest->reference_no,
            'samplingRequest' => $samplingRequest,
            // The app can request a product that has since been deactivated, so
            // the request's own product is always in the list — otherwise the
            // dropdown would silently default to some other product.
            'products'        => $this->productsWithDealerPrice([
                $samplingRequest->product_id,
                $samplingRequest->requested_product_id,
            ]),
            'employees'       => User::marketingEmployees()->orderBy('name')->get(['id', 'name']),
            'thread'          => $thread,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  ACTIONS
    // ══════════════════════════════════════════════════════════════════

    public function update(UpdateSamplingRequestRequest $request, $id)
    {
        $samplingRequest = SamplingRequest::findOrFail($id);
        $this->service->update($samplingRequest, $request->validated());

        return $this->done('Request details updated.', $samplingRequest);
    }

    public function approve(ApproveSamplingRequestRequest $request, $id)
    {
        $samplingRequest = SamplingRequest::findOrFail($id);
        $this->service->approve($samplingRequest, $request->validated());

        return $this->done('Request ' . $samplingRequest->reference_no . ' approved for ' . $samplingRequest->approved_qty . ' Kg.', $samplingRequest);
    }

    public function decline(Request $request, $id)
    {
        $data = $request->validate(
            ['decline_reason' => 'required|string|max:2000'],
            ['decline_reason.required' => 'Please enter the reason for declining.']
        );

        $samplingRequest = SamplingRequest::findOrFail($id);
        $this->service->decline($samplingRequest, $data);

        return $this->done('Request ' . $samplingRequest->reference_no . ' declined.', $samplingRequest);
    }

    public function markReady(Request $request, $id)
    {
        $data = $request->validate(['ready_remarks' => 'nullable|string|max:2000']);

        $samplingRequest = SamplingRequest::findOrFail($id);
        $this->service->markReady($samplingRequest, $data);

        return $this->done('Request ' . $samplingRequest->reference_no . ' is marked ready for dispatch.', $samplingRequest);
    }

    // ══════════════════════════════════════════════════════════════════
    //  QUERY THREAD
    // ══════════════════════════════════════════════════════════════════

    /**
     * The whole thread for one request. Opening it marks the employee's
     * messages as read, which is what clears the unread badge.
     */
    public function queries($id)
    {
        $samplingRequest = SamplingRequest::findOrFail($id);

        $thread = app(SamplingQueryService::class)
            ->thread($samplingRequest, SamplingRequestQuery::SENDER_ADMIN);

        return response()->json(['success' => true, 'reference_no' => $samplingRequest->reference_no] + $thread);
    }

    /** Post a reply as the admin. Refused once the request is closed or declined. */
    public function raiseQuery(Request $request, $id)
    {
        $data = $request->validate(
            ['message' => 'required|string|min:1|max:2000'],
            ['message.required' => 'Please type your query before sending.']
        );

        $samplingRequest = SamplingRequest::findOrFail($id);
        $service         = app(SamplingQueryService::class);

        $query = $service->post(
            $samplingRequest,
            SamplingRequestQuery::SENDER_ADMIN,
            \Auth::check() ? \Auth::user()->id : null,
            $data['message']
        );

        return response()->json([
            'success' => true,
            'query'   => $query->fresh('sender')->toThreadArray(),
            'total'   => $samplingRequest->queries()->count(),
            'unread'  => $service->unreadForAdmin($samplingRequest->id),
        ]);
    }

    /**
     * Select2 source shared by the request detail and the dispatch screens.
     * Each result carries a formatted address so picking a party fills it in.
     */
    public function parties(Request $request)
    {
        $request->validate(['type' => 'required|in:dealer,customer,user']);

        $term = trim((string) $request->q);

        if ($request->type === 'dealer') {
            $results = Dealer::where('status', 1)
                ->whereNull('parent_id')
                ->when($term !== '', function ($q) use ($term) {
                    $q->where(function ($q) use ($term) {
                        $q->where('business_name', 'like', "%$term%")
                          ->orWhere('name', 'like', "%$term%")
                          ->orWhere('city', 'like', "%$term%")
                          ->orWhere('owner_mobile', 'like', "%$term%");
                    });
                })
                ->orderBy('business_name')
                ->limit(30)
                ->get(['id', 'business_name', 'name', 'city', 'owner_mobile', 'address', 'shipping_address'])
                ->map(function ($d) {
                    $displayName = $d->business_name ?: $d->name;
                    return [
                        'id'      => $d->id,
                        'text'    => $displayName . ($d->city ? ' — ' . $d->city : ''),
                        'address' => $this->formatAddress($displayName, $d->shipping_address ?: $d->address, $d->owner_mobile),
                    ];
                });
        } elseif ($request->type === 'customer') {
            $results = Customer::where('status', 1)
                ->when($term !== '', function ($q) use ($term) {
                    $q->where(function ($q) use ($term) {
                        $q->where('name', 'like', "%$term%")
                          ->orWhere('mobile', 'like', "%$term%");
                    });
                })
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'mobile', 'address'])
                ->map(function ($c) {
                    return [
                        'id'      => $c->id,
                        'text'    => $c->name,
                        'address' => $this->formatAddress($c->name, $c->address, $c->mobile),
                    ];
                });
        } else {
            $results = User::marketingEmployees()
                ->when($term !== '', function ($q) use ($term) {
                    $q->where('users.name', 'like', "%$term%");
                })
                ->orderBy('users.name')
                ->limit(30)
                ->get(['id', 'name', 'mobile', 'correspondence_address'])
                ->map(function ($u) {
                    return [
                        'id'      => $u->id,
                        'text'    => $u->name,
                        'address' => $this->formatAddress($u->name, $u->correspondence_address, $u->mobile),
                    ];
                });
        }

        return response()->json(['results' => $results->values()]);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /**
     * Active products carrying today's dealer price, so the detail screen can
     * show the value of what is being approved the way the old screen did.
     */
    /**
     * Active products, plus any extra ids asked for.
     *
     * The extras are how a request keeps pointing at its own product after that
     * product has been deactivated: it stays selectable (flagged inactive) so
     * the admin sees what was actually asked for instead of the first product
     * in the list.
     *
     * @param  array $includeIds products to keep whatever their status
     */
    private function productsWithDealerPrice(array $includeIds = [])
    {
        $includeIds = array_filter(array_unique($includeIds));

        $products = Product::where(function ($q) use ($includeIds) {
                $q->where('status', 1);
                if ($includeIds) {
                    $q->orWhereIn('id', $includeIds);
                }
            })
            ->with(['pricings' => function ($q) {
                $q->whereDate('price_date', '<=', date('Y-m-d'))->orderBy('price_date', 'DESC');
            }])
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'product_code', 'physical_form', 'status']);

        $products->each(function ($product) {
            $product->dealer_price = optional($product->pricings->first())->dealer_price ?: 0;
            $product->pack_sizes   = SamplingRequest::packSizesFor($product->physical_form);
            $product->is_inactive  = (int) $product->status !== 1;
            unset($product->pricings);
        });

        return $products;
    }

    /** Same "Name / Address / M. mobile" layout the app stores in dispatch_address. */
    private function formatAddress($name, $address, $mobile)
    {
        return implode("\n", array_filter([
            trim((string) $name),
            trim((string) $address),
            $mobile ? 'M. ' . $mobile : null,
        ]));
    }

    private function done($message, SamplingRequest $samplingRequest)
    {
        Session::flash('flash_message_success', $message);

        return response()->json([
            'status'  => true,
            'message' => $message,
            'url'     => route('admin.sampling-requests.show', $samplingRequest->id),
        ]);
    }
}
