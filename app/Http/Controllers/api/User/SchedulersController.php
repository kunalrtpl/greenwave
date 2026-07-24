<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthToken;
use App\UserScheduler;
use Carbon\Carbon;

class SchedulersController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $token      = $request->header('Authorization');
            $resp       = AuthToken::verifyUser($token);
            $this->resp = $resp;
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resolve FK columns independently — whichever keys are present
     * and non-empty in the payload get saved. related_to is just free text
     * and has no bearing on which FK gets set.
     */
    private function resolveForeignKeys(array $data): array
    {
        return [
            'dealerId'                  => isset($data['dealer_id']) && !empty($data['dealer_id'])
                ? (int) $data['dealer_id'] : null,

            'customerId'                => isset($data['customer_id']) && !empty($data['customer_id'])
                ? (int) $data['customer_id'] : null,

            'customerRegisterRequestId' => isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])
                ? (int) $data['customer_register_request_id'] : null,

            'userDvrId'                 => isset($data['user_dvr_id']) && !empty($data['user_dvr_id'])
                ? (int) $data['user_dvr_id'] : null,

            'otherCustomerName'         => isset($data['other_customer_name']) && !empty($data['other_customer_name'])
                ? $data['other_customer_name'] : null,
        ];
    }

    /**
     * Eager-load scheduler with limited relation fields.
     *
     * Relations included:
     *   dealer                    → id, business_name, name
     *   customer                  → id, name, contact_person_name
     *   customer_register_request → id, name, contact_person_name
     *   previous_scheduler        → with same limited sub-relations
     *   next_scheduler            → with same limited sub-relations
     *
     * userDvr is commented out — enable when ready.
     */
    private function loadScheduler(int $id): UserScheduler
    {
        return UserScheduler::with($this->withRelations())->findOrFail($id);
    }

    /**
     * Centralised eager-load definition reused by index, show, store, update.
     */
    private function withRelations(): array
    {
        return [
            // dealer — limited fields only
            'dealer:id,business_name,name',

            // customer — limited fields only
            'customer:id,name,contact_person_name',

            // customer_register_request — limited fields only
            'customer_register_request:id,name,contact_person_name',

            // previous scheduler with its own limited sub-relations
            'previous_scheduler' => function ($query) {
                $query->with([
                    'dealer:id,business_name,name',
                    'customer:id,name,contact_person_name',
                    'customer_register_request:id,name,contact_person_name',
                ]);
            },

            // next scheduler with its own limited sub-relations
            'next_scheduler' => function ($query) {
                $query->with([
                    'dealer:id,business_name,name',
                    'customer:id,name,contact_person_name',
                    'customer_register_request:id,name,contact_person_name',
                ]);
            },

            // 'userDvr', // ← commented out until user_dvr relation is ready
        ];
    }

    // ─── 1. List Schedulers ───────────────────────────────────────────────────
    /**
     * GET /api/v2/schedulers
     *
     * Query params (all optional):
     *   date        string  YYYY-MM-DD  — filter by date; omit to get unscheduled
     *   user_id     integer             — defaults to authenticated user
     *   related_to  string              — filter by related_to text (LIKE match not needed, exact)
     *   status      string              — Open | Pending | Done | Cancelled
     */
    public function index(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        $data = $request->all();

        $userId = isset($data['user_id']) && !empty($data['user_id'])
            ? (int) $data['user_id']
            : $resp['user']['id'];

        $query = UserScheduler::with($this->withRelations())
            ->where('user_id', $userId);

        // ── Date filter ───────────────────────────────────────────────────────
        if (!empty($data['date'])) {
            $query->where('scheduler_date', $data['date'])
                  ->orderBy('scheduler_time', 'ASC');
        } else {
            //$query->whereNull('scheduler_date');
        }

        // ── Optional filters ──────────────────────────────────────────────────
        if (!empty($data['related_to'])) {
            $query->where('related_to', $data['related_to']);
        }

        if (!empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        $schedulers = $query->orderBy('id', 'DESC')->get();

        $result  = ['schedulers' => $schedulers];
        $message = "Schedulers fetched successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 2. Create Scheduler ─────────────────────────────────────────────────
    /**
     * POST /api/v2/schedulers
     * Content-Type: application/json  or  multipart/form-data
     *
     * Fields:
     *   related_to                    string   optional — plain free text, no validation tied to it
     *   dealer_id                     integer  optional — saved if present & non-empty
     *   customer_id                   integer  optional — saved if present & non-empty
     *   customer_register_request_id  integer  optional — saved if present & non-empty
     *   user_dvr_id                   integer  optional — saved if present & non-empty
     *   other_customer_name           string   optional — saved if present & non-empty
     *   previous_scheduler_id         integer  optional
     *   subject                       string   optional
     *   scheduler_date                string   YYYY-MM-DD  required
     *   scheduler_time                string   HH:MM       required
     *   description                   string   required
     *   status                        string   optional — default Open
     */
    public function store(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        // ── Validation — no conditional FK requirements tied to related_to ────
        $rules = [
            'related_to'                   => 'nullable|string|max:100',
            'dealer_id'                    => 'nullable|integer|exists:dealers,id',
            'customer_id'                  => 'nullable|integer|exists:customers,id',
            'customer_register_request_id' => 'nullable|integer|exists:customer_register_requests,id',
            'user_dvr_id'                  => 'nullable|integer|exists:user_dvrs,id',
            'other_customer_name'          => 'nullable|string|max:255',
            'previous_scheduler_id'        => 'nullable|integer|exists:user_schedulers,id',
            'subject'                      => 'nullable|string|max:255',
            'scheduler_date'               => 'required|date_format:Y-m-d',
            'scheduler_time'               => 'required|date_format:H:i',
            'description'                  => 'required|string',
            'status'                       => 'nullable|string',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        $data = $request->all();
        $fks  = $this->resolveForeignKeys($data);

        $scheduler = UserScheduler::create([
            'user_id'                      => $resp['user']['id'],
            'related_to'                   => $request->input('related_to'),
            'dealer_id'                    => $fks['dealerId'],
            'customer_id'                  => $fks['customerId'],
            'customer_register_request_id' => $fks['customerRegisterRequestId'],
            'user_dvr_id'                  => $fks['userDvrId'],
            'other_customer_name'          => $fks['otherCustomerName'],
            'previous_scheduler_id'        => $request->input('previous_scheduler_id'),
            'subject'                      => $request->input('subject'),
            'scheduler_date'               => $request->input('scheduler_date'),
            'scheduler_time'               => $request->input('scheduler_time'),
            'description'                  => $request->input('description'),
            'status'                       => $request->input('status', 'Open'),
        ]);

        $result  = ['scheduler' => $this->loadScheduler($scheduler->id)];
        $message = "Scheduler created successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 3. Show Single Scheduler ─────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        $scheduler = UserScheduler::with($this->withRelations())
            ->where('id', $id)
            ->where('user_id', $resp['user']['id'])
            ->first();

        if (!$scheduler) {
            return response()->json(apiErrorResponse("Scheduler not found."), 422);
        }

        $result  = ['scheduler' => $scheduler];
        $message = "Scheduler fetched successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 4. Update Scheduler ─────────────────────────────────────────────────
    /**
     * POST /api/v2/schedulers/{id}
     * All fields optional — only send what needs changing.
     * Each FK key is saved independently if present & non-empty in payload.
     * Sending an empty value for an FK key clears it (sets to null).
     */
    public function update(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        $scheduler = UserScheduler::where('id', $id)
            ->where('user_id', $resp['user']['id'])
            ->first();

        if (!$scheduler) {
            return response()->json(apiErrorResponse("Scheduler not found."), 422);
        }

        // ── Validation ────────────────────────────────────────────────────────
        $rules = [
            'related_to'                   => 'nullable|string|max:100',
            'dealer_id'                    => 'nullable|integer|exists:dealers,id',
            'customer_id'                  => 'nullable|integer|exists:customers,id',
            'customer_register_request_id' => 'nullable|integer|exists:customer_register_requests,id',
            'user_dvr_id'                  => 'nullable|integer|exists:user_dvrs,id',
            'other_customer_name'          => 'nullable|string|max:255',
            'previous_scheduler_id'        => 'nullable|integer|exists:user_schedulers,id',
            'subject'                      => 'nullable|string|max:255',
            'scheduler_date'               => 'sometimes|date_format:Y-m-d',
            'scheduler_time'               => 'sometimes|date_format:H:i',
            'description'                  => 'sometimes|string',
            'status'                       => 'nullable|string',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        $data       = $request->all();
        $updateData = [];

        if ($request->has('related_to'))                   $updateData['related_to']                   = $request->input('related_to');

        // Each FK key independently — present in payload → save/clear it
        if ($request->has('dealer_id'))                     $updateData['dealer_id']                    = !empty($data['dealer_id']) ? (int) $data['dealer_id'] : null;
        if ($request->has('customer_id'))                   $updateData['customer_id']                  = !empty($data['customer_id']) ? (int) $data['customer_id'] : null;
        if ($request->has('customer_register_request_id'))  $updateData['customer_register_request_id'] = !empty($data['customer_register_request_id']) ? (int) $data['customer_register_request_id'] : null;
        if ($request->has('user_dvr_id'))                   $updateData['user_dvr_id']                  = !empty($data['user_dvr_id']) ? (int) $data['user_dvr_id'] : null;
        if ($request->has('other_customer_name'))           $updateData['other_customer_name']          = !empty($data['other_customer_name']) ? $data['other_customer_name'] : null;

        if ($request->has('previous_scheduler_id')) $updateData['previous_scheduler_id'] = $request->input('previous_scheduler_id');
        if ($request->has('subject'))               $updateData['subject']               = $request->input('subject');
        if ($request->has('scheduler_date'))        $updateData['scheduler_date']        = $request->input('scheduler_date');
        if ($request->has('scheduler_time'))        $updateData['scheduler_time']        = $request->input('scheduler_time');
        if ($request->has('description'))           $updateData['description']           = $request->input('description');
        if ($request->has('status'))                $updateData['status']                = $request->input('status');

        $scheduler->update($updateData);

        $result  = ['scheduler' => $this->loadScheduler($scheduler->id)];
        $message = "Scheduler updated successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 5. Update Status Only ────────────────────────────────────────────────
    /**
     * POST /api/v2/schedulers/{id}/status
     *
     * Fields:
     *   status  string  required — Open | Pending | Done | Cancelled
     */
    public function updateStatus(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        $validator = \Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        $scheduler = UserScheduler::where('id', $id)
            ->where('user_id', $resp['user']['id'])
            ->first();

        if (!$scheduler) {
            return response()->json(apiErrorResponse("Scheduler not found."), 422);
        }

        $scheduler->update(['status' => $request->input('status')]);

        $message = "Scheduler status updated successfully.";
        return response()->json(apiSuccessResponse($message, []), 200);
    }

    // ─── 6. Update Next Scheduler ─────────────────────────────────────────────
    /**
     * POST /api/v2/schedulers/{id}/next-scheduler
     *
     * Fields:
     *   next_scheduler_id  integer  required
     */
    public function updateNextScheduler(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        $validator = \Validator::make($request->all(), [
            'next_scheduler_id' => 'required|integer|exists:user_schedulers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        $scheduler = UserScheduler::where('id', $id)
            ->where('user_id', $resp['user']['id'])
            ->first();

        if (!$scheduler) {
            return response()->json(apiErrorResponse("Scheduler not found."), 422);
        }

        $scheduler->update(['next_scheduler_id' => $request->input('next_scheduler_id')]);

        $message = "Next scheduler linked successfully.";
        return response()->json(apiSuccessResponse($message, []), 200);
    }

    // ─── 7. Delete Scheduler ─────────────────────────────────────────────────

    public function destroy(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            return response()->json(apiErrorResponse("Unauthorized. Please try again after sometime."), 422);
        }

        $scheduler = UserScheduler::where('id', $id)
            ->where('user_id', $resp['user']['id'])
            ->first();

        if (!$scheduler) {
            return response()->json(apiErrorResponse("Scheduler not found."), 422);
        }

        $scheduler->delete();

        $message = "Scheduler deleted successfully.";
        return response()->json(apiSuccessResponse($message, []), 200);
    }
}