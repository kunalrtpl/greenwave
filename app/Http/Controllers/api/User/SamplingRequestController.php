<?php

namespace App\Http\Controllers\api\User;

use App\AuthToken;
use App\Http\Controllers\Controller;
use App\SamplingRequest;
use App\SamplingRequestQuery;
use App\Services\SamplingQueryService;
use App\Services\SamplingRequestService;
use Illuminate\Http\Request;
use Validator;

/**
 * Sample requests raised from the executive app.
 *
 * Both the old and the new app talk to this one controller, and both write the
 * same `sampling_requests` rows, so whatever an executive raises shows up on
 * the revamped admin screens regardless of which build they are running.
 *
 *   createSampleRequest()    POST /api/executive/create-sample-request
 *       The ORIGINAL payload, unchanged. Kept so builds already in the field
 *       keep working. It used to write the legacy samplings / sampling_items
 *       tables through Sampling::createOrEditFreeSample(); it now maps the same
 *       payload onto the new tables instead.
 *
 *   createSamplingRequest()  POST /api/executive/create-sampling-request
 *       The payload for the NEW app. Same fields, plus dispatch_dealer_id and
 *       dispatch_customer_id so the request can name where it should go.
 *
 *   samplingRequests()       GET  /api/executive/sampling-requests
 *       The executive's own requests, each carrying its own status, the qty
 *       breakdown (requested / approved / dispatched / pending), what has been
 *       dispatched against it and the unread count on its query thread.
 *
 *   getQueries()  GET  /api/executive/sampling-requests/{id}/queries
 *   sendQuery()   POST /api/executive/sampling-requests/{id}/query
 *       The query thread with the admin, one call each way. Posting is refused
 *       once the request is closed or declined; the history stays readable.
 *
 * Dispatched material and its receipt confirmation are a separate controller,
 * because receipt is not a request status: a closed request stays closed and
 * is_received is a flag on the dispatched line.
 * @see \App\Http\Controllers\api\User\SampleDispatchController
 *
 * Both create endpoints end up in SamplingRequestService::createFromApi(), so
 * one post carrying several products becomes one independent request per
 * product (SR-n.1, SR-n.2 …). When the new app ships, the app developer points
 * at /create-sampling-request and nothing else has to change.
 *
 * @see \App\Services\SamplingRequestService
 */
class SamplingRequestController extends Controller
{
    /** Resolved from the Authorization header, the same as every executive route. */
    private $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  OLD APP  — original payload, new tables
    // ══════════════════════════════════════════════════════════════════

    /**
     * The payload older builds already send. Validation stays as loose as it
     * was so nothing that used to save starts failing; the extra fields the
     * new endpoint validates are simply accepted as-is here.
     *
     * Legacy keys that the new tables have no column for are dropped rather
     * than rejected:
     *   required_through, request_type  — the two-step flow has no equivalent
     *   item_id, is_delete              — see the note on editing below
     *
     * Editing is not supported: a request is now one row per product with its
     * own lifecycle, so there is nothing for a legacy `id` to point at. A post
     * carrying `id` is refused with a clear message rather than silently
     * creating a duplicate.
     */
    public function createSampleRequest(Request $request)
    {
        if (!$request->isMethod('post')) {
            return response()->json(apiErrorResponse('GET not supported for this route'), 422);
        }
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Unable to fetch. Please try again after sometime'), 422);
        }

        if ($request->filled('id')) {
            return response()->json(apiErrorResponse(
                // /delete-sample-request only knows the legacy samplings table,
                // so it is not offered as the way out of this.
                'Editing a sample request is no longer supported. Please raise a new request instead.'
            ), 422);
        }

        // The original rules, unchanged.
        $validator = Validator::make($request->all(), [
            'items'              => 'sometimes|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        return $this->create($this->fromLegacyPayload($request->all()));
    }

    /**
     * Maps the original payload onto what the service expects. Almost every
     * key already lines up; only the ones below need a decision.
     */
    private function fromLegacyPayload(array $data)
    {
        // The old payload has one free-text `remarks` for the whole request and
        // no purpose field of its own, so it stands in for the purpose when
        // nothing better was sent.
        if (empty($data['purpose_reason_for_request']) && !empty($data['remarks'])) {
            $data['purpose_reason_for_request'] = $data['remarks'];
        }

        // Older builds never name a dispatch party, only a free-text address,
        // which leaves the sample booked to the executive who raised it.
        $data['dispatch_dealer_id']   = isset($data['dispatch_dealer_id']) ? $data['dispatch_dealer_id'] : null;
        $data['dispatch_customer_id'] = isset($data['dispatch_customer_id']) ? $data['dispatch_customer_id'] : null;

        unset($data['required_through'], $data['request_type'], $data['action'], $data['sample_date']);

        if (!empty($data['items'])) {
            $data['items'] = array_map(function ($item) {
                unset($item['item_id'], $item['is_delete']);
                return $item;
            }, $data['items']);
        }

        return $data;
    }

    // ══════════════════════════════════════════════════════════════════
    //  NEW APP
    // ══════════════════════════════════════════════════════════════════

    /**
     * The payload the new app posts. Same shape as the old one plus
     * dispatch_dealer_id / dispatch_customer_id, and validated properly.
     */
    public function createSamplingRequest(Request $request)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Unable to fetch. Please try again after sometime'), 422);
        }

        $validator = Validator::make($request->all(), [
            'sample_type'            => 'nullable|in:free,paid',
            'customer_id'            => 'nullable|exists:customers,id',
            'gst'                    => 'nullable|numeric',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.pack_size_info' => 'nullable|string|max:100',
            'items.*.pack_size'      => 'nullable|numeric|min:0',
            'items.*.no_of_packs'    => 'nullable|integer|min:0',
            'items.*.qty'            => 'required|numeric|min:0.001',
            'items.*.remarks'        => 'nullable|string|max:2000',

            'purpose_reason_for_request' => 'nullable|string|max:5000',
            'application_plan'           => 'nullable|string|max:5000',

            'dispatch_dealer_id'   => 'nullable|exists:dealers,id',
            'dispatch_customer_id' => 'nullable|exists:customers,id',
            'dispatch_to'          => 'nullable|string|max:255',
            'dispatch_address'     => 'nullable|string|max:2000',

            'is_specific_customer' => 'nullable|boolean',
            'product_known'        => 'nullable|boolean',
            'target_competitor'    => 'nullable|boolean',

            'competitor_category'               => 'nullable|string|max:255',
            'competitor_product_name'           => 'nullable|string|max:255',
            'competitor_make'                   => 'nullable|string|max:255',
            'competitor_dealer_price'           => 'nullable|numeric',
            'competitor_customer_price'         => 'nullable|numeric',
            'competitor_dosage'                 => 'nullable|string|max:100',
            'competitor_monthly_requirement_kg' => 'nullable|numeric',
            'competitor_specialty'              => 'nullable|string|max:5000',
            'competitor_application_details'    => 'nullable|string|max:5000',
            'competitor_expectation'            => 'nullable|string|max:5000',
        ], [
            'items.required'              => 'Please add at least one product.',
            'items.*.product_id.required' => 'Please select the product.',
            'items.*.qty.required'        => 'Please enter the qty.',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        return $this->create($validator->validated());
    }

    // ══════════════════════════════════════════════════════════════════
    //  LISTING
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET /api/executive/sampling-requests
     *
     * The logged-in executive's own requests, newest request first, one row
     * per product (SR-n.m) carrying its own status:
     *
     *   pending   waiting for the admin
     *   approved  approved, waiting to be dispatched (is_ready says whether
     *             the stores team has it packed)
     *   closed    the whole approved qty has been dispatched
     *   declined  refused, with decline_reason
     *
     * Receipt is deliberately NOT a status: a closed request stays closed, and
     * whether the material arrived is the `receipt` block and the is_received
     * flag on each dispatch line — see SampleDispatchController.
     *
     * Filters: status (one of the four, or `all`), product_id, keyword,
     * from_date / to_date on the request date. `counts` always carries the
     * totals for all four statuses so the app can badge its tabs.
     */
    public function samplingRequests(Request $request)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $userId = $this->resp['user']['id'];

        $query = SamplingRequest::with([
                'product:id,product_name,product_code',
                'requested_product:id,product_name,product_code',
                'customer:id,name',
                'dispatch_dealer:id,name,business_name',
                'dispatch_customer:id,name',
                'dispatch_user:id,name',
                'approved_by:id,name',
                'dispatches.product:id,product_name,product_code',
                'dispatches.sampling_request:id,reference_no,is_voluntarily,user_id',
            ])
            ->where('user_id', $userId);

        $status = $request->input('status');
        if ($status && $status !== 'all' && isset(SamplingRequest::STATUSES[$status])) {
            $query->where('status', $status);
        }

        if ($request->filled('product_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('product_id', $request->product_id)
                  ->orWhere('requested_product_id', $request->product_id);
            });
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('reference_no', 'like', '%' . $keyword . '%')
                  ->orWhereHas('product', function ($p) use ($keyword) {
                      $p->where('product_name', 'like', '%' . $keyword . '%');
                  })
                  ->orWhereHas('requested_product', function ($p) use ($keyword) {
                      $p->where('product_name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('request_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('request_date', '<=', $request->to_date);
        }

        $requests = $query->orderBy('request_group_no', 'DESC')
            ->orderBy('line_no', 'ASC')
            ->get();

        // One query for every row's thread counts rather than two per row.
        $counts = app(SamplingQueryService::class)->countsFor($requests->pluck('id')->all());
        $unread = $this->unreadForEmployee($requests->pluck('id')->all());

        $result = [
            'status'           => $status ?: 'all',
            'counts'           => $this->statusCounts($userId),
            'sampling_requests' => $requests->map(function ($r) use ($counts, $unread) {
                return $r->toApiArray([
                    'total'  => isset($counts['total'][$r->id]) ? $counts['total'][$r->id] : 0,
                    'unread' => isset($unread[$r->id]) ? $unread[$r->id] : 0,
                ]);
            })->values(),
        ];

        return response()->json(apiSuccessResponse('Fetched successfully', $result), 200);
    }

    /** How many of the executive's requests sit in each status. */
    private function statusCounts($userId)
    {
        $counts = SamplingRequest::selectRaw('status, COUNT(*) as n')
            ->where('user_id', $userId)
            ->groupBy('status')
            ->pluck('n', 'status')
            ->all();

        $result = ['all' => array_sum($counts)];
        foreach (array_keys(SamplingRequest::STATUSES) as $status) {
            $result[$status] = isset($counts[$status]) ? (int) $counts[$status] : 0;
        }

        return $result;
    }

    /** Admin messages the app has not opened yet, keyed by request id. */
    private function unreadForEmployee(array $requestIds)
    {
        if (!$requestIds) {
            return [];
        }

        return SamplingRequestQuery::selectRaw('sampling_request_id, COUNT(*) as n')
            ->whereIn('sampling_request_id', $requestIds)
            ->unreadByEmployee()
            ->groupBy('sampling_request_id')
            ->pluck('n', 'sampling_request_id')
            ->all();
    }

    // ══════════════════════════════════════════════════════════════════
    //  QUERY THREAD
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET /api/executive/sampling-requests/{id}/queries
     *
     * The whole thread for one of the executive's own requests. Opening it
     * marks the admin's messages as read, which clears the app's unread badge.
     */
    public function getQueries($id)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $request = $this->ownRequest($id);
        if (!$request) {
            return response()->json(apiErrorResponse('Sample request not found'), 404);
        }

        $thread = app(SamplingQueryService::class)
            ->thread($request, SamplingRequestQuery::SENDER_EMPLOYEE);

        return response()->json(apiSuccessResponse('Fetched successfully', $thread + [
            'reference_no' => $request->reference_no,
            'status'       => $request->status,
        ]), 200);
    }

    /**
     * POST /api/executive/sampling-requests/{id}/query
     *
     * Ask the admin something. Refused once the request is closed or declined
     * — the thread stays readable but is shut.
     */
    public function sendQuery(Request $request, $id)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $samplingRequest = $this->ownRequest($id);
        if (!$samplingRequest) {
            return response()->json(apiErrorResponse('Sample request not found'), 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:1|max:2000',
        ], [
            'message.required' => 'Please type your query before sending.',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        if (!$samplingRequest->acceptsQueries()) {
            return response()->json(apiErrorResponse($samplingRequest->queriesClosedReason()), 422);
        }

        $service = app(SamplingQueryService::class);

        $query = $service->post(
            $samplingRequest,
            SamplingRequestQuery::SENDER_EMPLOYEE,
            $this->resp['user']['id'],
            $request->message
        );

        return response()->json(apiSuccessResponse('Query sent successfully', [
            'query'       => $query->fresh('sender:id,name')->toThreadArray(),
            'total'       => $samplingRequest->queries()->count(),
            'unread_user' => $service->unreadForEmployee($samplingRequest->id),
        ]), 200);
    }

    /** A request only belongs to the executive whose token was used. */
    private function ownRequest($id)
    {
        return SamplingRequest::where('id', $id)
            ->where('user_id', $this->resp['user']['id'])
            ->first();
    }

    // ══════════════════════════════════════════════════════════════════
    //  SHARED
    // ══════════════════════════════════════════════════════════════════

    /** Both create endpoints land here, so both store exactly the same rows. */
    private function create(array $data)
    {
        try {
            $created = app(SamplingRequestService::class)
                ->createFromApi($data, $this->resp['user']['id']);
        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }

        $result = [
            'reference_nos' => $created->pluck('reference_no')->all(),
            'requests'      => $created->map(function ($r) {
                return [
                    'id'            => $r->id,
                    'reference_no'  => $r->reference_no,
                    'product_id'    => $r->product_id,
                    'requested_qty' => $r->requested_qty,
                    'status'        => $r->status,
                ];
            })->values(),
        ];

        return response()->json(
            apiSuccessResponse('Sample request has been created successfully', $result),
            200
        );
    }

    private function authenticated()
    {
        return !empty($this->resp['status']) && isset($this->resp['user']);
    }
}
