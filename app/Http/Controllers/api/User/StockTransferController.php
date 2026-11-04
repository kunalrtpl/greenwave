<?php

namespace App\Http\Controllers\api\User;

use App\AuthToken;
use App\Http\Controllers\Controller;
use App\StockTransfer;
use App\Services\StockTransferService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

/**
 * Stock Transfer — one marketing employee hands free-sample stock to another.
 *
 * The whole module lives here; there is no admin screen for it.
 *
 *   stock()            GET  /api/executive/stock-transfers/stock
 *       What the caller can offer: their products, the qty free to transfer
 *       and the qty already blocked behind pending requests.
 *
 *   employees()        GET  /api/executive/stock-transfers/employees
 *       The marketing employees stock may be transferred to.
 *
 *   store()            POST /api/executive/stock-transfers
 *       Raise a transfer. The qty is blocked on the sender's stock
 *       immediately, so the same material cannot be offered twice, and the
 *       receiving employee is emailed.
 *
 *   index()            GET  /api/executive/stock-transfers
 *       Everything the caller is part of — ?direction=sent|received|all and
 *       ?status=pending|approved|declined|cancelled.
 *
 *   pendingApprovals() GET  /api/executive/stock-transfers/pending-approvals
 *       The dedicated list for the receiving employee: what is waiting on
 *       them right now, and nothing else.
 *
 *   show()             GET  /api/executive/stock-transfers/{id}
 *   approve()          POST /api/executive/stock-transfers/{id}/approve
 *   decline()          POST /api/executive/stock-transfers/{id}/decline
 *   cancel()           POST /api/executive/stock-transfers/{id}/cancel
 *
 * Who may do what: only the employee the stock was offered to can accept or
 * refuse it, and only the one who raised it can cancel — and only while it is
 * still pending. Everything else is a 422 that says why.
 *
 * @see \App\Services\StockTransferService
 */
class StockTransferController extends Controller
{
    /** @var StockTransferService */
    private $service;

    /** The resolved token, and the request it was resolved from. */
    private $resp;
    private $respRequest;

    public function __construct(StockTransferService $service)
    {
        $this->service = $service;
    }

    /**
     * Who is calling, worked out from the Authorization header of the request
     * being handled right now.
     *
     * Deliberately not resolved in the constructor: a controller instance can
     * outlive the request it was built for, and this module is the first one
     * where two different employees act on the same record, so a stale
     * identity here would hand one person's approvals to the other. The answer
     * is cached per request object, so the token is still looked up only once.
     */
    private function resp()
    {
        $request = request();

        if ($this->respRequest !== $request) {
            $this->respRequest = $request;
            $this->resp        = $request->header('Authorization')
                ? AuthToken::verifyUser($request->header('Authorization'))
                : null;
        }

        return $this->resp;
    }

    // ══════════════════════════════════════════════════════════════════
    //  FORM DATA
    // ══════════════════════════════════════════════════════════════════

    /** The caller's own stock, so the app can show what is transferable. */
    public function stock()
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $stock = $this->service->transferableStock($this->userId());

        return response()->json(apiSuccessResponse('Fetched successfully', [
            'stock'         => $stock,
            'total_available' => round($stock->sum('available_qty'), 3),
            'total_blocked'   => round($stock->sum('blocked_qty'), 3),
        ]), 200);
    }

    /** Marketing employees the caller may transfer to — never themselves. */
    public function employees()
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $employees = User::marketingEmployees()
            ->where('users.id', '!=', $this->userId())
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.mobile', 'users.email']);

        return response()->json(apiSuccessResponse('Fetched successfully', [
            'employees' => $employees,
        ]), 200);
    }

    // ══════════════════════════════════════════════════════════════════
    //  RAISING
    // ══════════════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $validator = Validator::make($request->all(), [
            'to_user_id'         => 'required|integer|exists:users,id',
            'transfer_date'      => 'nullable|date_format:Y-m-d',
            'remarks'            => 'nullable|string|max:2000',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|numeric|min:0.001',
        ], [
            'to_user_id.required'        => 'Please choose the employee to transfer to.',
            'items.required'             => 'Add at least one product to transfer.',
            'items.*.product_id.required' => 'Please select the product.',
            'items.*.qty.required'       => 'Please enter the qty.',
            'items.*.qty.min'            => 'Enter a transfer qty greater than zero.',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        try {
            $transfer = $this->service->create($validator->validated(), $this->userId());
        } catch (ValidationException $e) {
            return response()->json(apiErrorResponse($this->firstMessage($e)), 422);
        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }

        return response()->json(apiSuccessResponse(
            'Stock transfer ' . $transfer->reference_no . ' has been sent for approval.',
            ['stock_transfer' => $transfer->toApiArray($this->userId())]
        ), 200);
    }

    // ══════════════════════════════════════════════════════════════════
    //  LISTING
    // ══════════════════════════════════════════════════════════════════

    /**
     * Filters: direction (sent|received|all), status, from_date, to_date,
     * keyword (ST ref no, the other employee's name, a product name).
     */
    public function index(Request $request)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $userId    = $this->userId();
        $direction = $request->input('direction', 'all');

        $query = StockTransfer::with(['items.product:id,product_name,product_code',
                                      'from_user:id,name', 'to_user:id,name']);

        if ($direction === 'sent') {
            $query->where('from_user_id', $userId);
        } elseif ($direction === 'received') {
            $query->where('to_user_id', $userId);
        } else {
            $direction = 'all';
            $query->involving($userId);
        }

        $status = $request->input('status');
        if ($status && isset(StockTransfer::STATUSES[$status])) {
            $query->ofStatus($status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transfer_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transfer_date', '<=', $request->to_date);
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('reference_no', 'like', '%' . $keyword . '%')
                  ->orWhereHas('from_user', function ($u) use ($keyword) {
                      $u->where('name', 'like', '%' . $keyword . '%');
                  })
                  ->orWhereHas('to_user', function ($u) use ($keyword) {
                      $u->where('name', 'like', '%' . $keyword . '%');
                  })
                  ->orWhereHas('items.product', function ($p) use ($keyword) {
                      $p->where('product_name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        $transfers = $query->orderBy('id', 'DESC')->get();

        return response()->json(apiSuccessResponse('Fetched successfully', [
            'direction'       => $direction,
            'status'          => $status ?: 'all',
            'counts'          => $this->counts($userId),
            'stock_transfers' => $transfers->map(function ($t) use ($userId) {
                return $t->toApiArray($userId);
            })->values(),
        ]), 200);
    }

    /**
     * GET /api/executive/stock-transfers/pending-approvals
     *
     * The receiving employee's to-do list: transfers offered to them that are
     * still waiting on an answer. This is the one the app badges.
     */
    public function pendingApprovals()
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $userId = $this->userId();

        $transfers = StockTransfer::with(['items.product:id,product_name,product_code',
                                          'from_user:id,name', 'to_user:id,name'])
            ->awaiting($userId)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(apiSuccessResponse('Fetched successfully', [
            'pending_count'   => $transfers->count(),
            'stock_transfers' => $transfers->map(function ($t) use ($userId) {
                return $t->toApiArray($userId);
            })->values(),
        ]), 200);
    }

    public function show($id)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $transfer = $this->mine($id);
        if (!$transfer) {
            return response()->json(apiErrorResponse('Stock transfer not found'), 404);
        }

        return response()->json(apiSuccessResponse('Fetched successfully', [
            'stock_transfer' => $transfer->toApiArray($this->userId()),
        ]), 200);
    }

    // ══════════════════════════════════════════════════════════════════
    //  ACTIONS
    // ══════════════════════════════════════════════════════════════════

    /** The receiving employee accepts it; the sender is emailed. */
    public function approve($id)
    {
        return $this->act($id, function (StockTransfer $transfer) {
            return $this->service->approve($transfer, $this->userId());
        }, 'accepted');
    }

    /** The receiving employee refuses it, with a reason; the sender is emailed. */
    public function decline(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'decline_reason' => 'required|string|max:2000',
        ], [
            'decline_reason.required' => 'Please give a reason for refusing the stock.',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        return $this->act($id, function (StockTransfer $transfer) use ($request) {
            return $this->service->decline($transfer, $this->userId(), $request->all());
        }, 'refused');
    }

    /** The sender withdraws it while it is still pending. */
    public function cancel($id)
    {
        return $this->act($id, function (StockTransfer $transfer) {
            return $this->service->cancel($transfer, $this->userId());
        }, 'cancelled');
    }

    /** Shared shape for the three actions. */
    private function act($id, callable $action, $word)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $transfer = $this->mine($id);
        if (!$transfer) {
            return response()->json(apiErrorResponse('Stock transfer not found'), 404);
        }

        try {
            $transfer = $action($transfer);
        } catch (ValidationException $e) {
            return response()->json(apiErrorResponse($this->firstMessage($e)), 422);
        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }

        return response()->json(apiSuccessResponse(
            'Stock transfer ' . $transfer->reference_no . ' has been ' . $word . '.',
            ['stock_transfer' => $transfer->toApiArray($this->userId())]
        ), 200);
    }

    // ══════════════════════════════════════════════════════════════════
    //  SHARED
    // ══════════════════════════════════════════════════════════════════

    /** A transfer is only visible to the two employees on it. */
    private function mine($id)
    {
        return StockTransfer::with(['items.product:id,product_name,product_code',
                                    'from_user:id,name,email', 'to_user:id,name,email'])
            ->where('id', $id)
            ->involving($this->userId())
            ->first();
    }

    /** How many transfers sit in each status, both directions. */
    private function counts($userId)
    {
        $byStatus = StockTransfer::selectRaw('status, COUNT(*) as n')
            ->involving($userId)
            ->groupBy('status')
            ->pluck('n', 'status')
            ->all();

        $counts = ['all' => array_sum($byStatus)];
        foreach (array_keys(StockTransfer::STATUSES) as $status) {
            $counts[$status] = isset($byStatus[$status]) ? (int) $byStatus[$status] : 0;
        }

        // The one the app badges: waiting on this employee to answer.
        $counts['awaiting_my_approval'] = StockTransfer::awaiting($userId)->count();

        return $counts;
    }

    private function firstMessage(ValidationException $e)
    {
        foreach ($e->errors() as $messages) {
            return is_array($messages) ? reset($messages) : $messages;
        }

        return 'The stock transfer could not be saved.';
    }

    private function userId()
    {
        $resp = $this->resp();

        return $resp['user']['id'];
    }

    private function authenticated()
    {
        $resp = $this->resp();

        return !empty($resp['status']) && isset($resp['user']);
    }
}
