<?php

namespace App\Http\Controllers\api\User;

use App\AuthToken;
use App\Http\Controllers\Controller;
use App\SamplingDispatch;
use App\Services\SamplingDispatchService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

/**
 * Sample material on its way to the executive, and the receipt confirmation.
 *
 * Dispatching from the admin panel only puts material in transit. The app
 * shows what is on its way and the executive confirms it arrived, which is
 * what turns their in-transit stock into stock in hand — the same two steps
 * the old sample-in-transit-materials /
 * update-free-sample-material-delivery pair had, now on the revamped tables.
 *
 *   dispatches()    GET  /api/executive/sample-dispatches
 *       Default is what has NOT been received yet (?status=pending).
 *       ?status=received for the history, ?status=all for both.
 *       ?group_by=challan nests the lines under their challan instead.
 *
 *   markReceived()  POST /api/executive/sample-dispatches/mark-received
 *       dispatch_ids[] — confirms arrival. Only the executive's own lines,
 *       and only once each: a line already confirmed is reported back as
 *       already_received and its stock is not moved again.
 *
 * A dispatch is the executive's when it was addressed to them or when it went
 * out against a request they raised — a sample sent straight to their customer
 * still came out of their in-transit stock, so it is still theirs to confirm.
 *
 * @see \App\Services\SamplingDispatchService::markReceived()
 */
class SampleDispatchController extends Controller
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
    //  LISTING
    // ══════════════════════════════════════════════════════════════════

    /**
     * What has been dispatched to the logged-in executive.
     *
     * Filters: status (pending|received|all), product_id, keyword
     * (challan no / LR no / dispatch ref / SR ref / product).
     */
    public function dispatches(Request $request)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $userId = $this->resp['user']['id'];
        $status = $request->input('status', 'pending');

        $query = SamplingDispatch::with([
                'product:id,product_name,product_code',
                'sampling_request:id,reference_no,is_voluntarily,user_id',
                'dispatch_dealer:id,name,business_name',
                'dispatch_customer:id,name',
                'dispatch_user:id,name',
            ])
            ->forEmployee($userId);

        if ($status === 'received') {
            $query->received();
        } elseif ($status !== 'all') {
            $status = 'pending';
            $query->notReceived();
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('dispatch_ref_no', 'like', '%' . $keyword . '%')
                  ->orWhere('invoice_no', 'like', '%' . $keyword . '%')
                  ->orWhere('lr_no', 'like', '%' . $keyword . '%')
                  ->orWhere('batch_no', 'like', '%' . $keyword . '%')
                  ->orWhereHas('sampling_request', function ($r) use ($keyword) {
                      $r->where('reference_no', 'like', '%' . $keyword . '%');
                  })
                  ->orWhereHas('product', function ($p) use ($keyword) {
                      $p->where('product_name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        $dispatches = $query->orderBy('dispatch_date', 'DESC')->orderBy('id', 'DESC')->get();
        $lines      = $dispatches->map(function ($d) { return $d->toApiArray(); })->values();

        $result = [
            'status'     => $status,
            'counts'     => $this->counts($userId),
            'total_qty'  => round($lines->sum('dispatch_qty'), 3),
            'dispatches' => $lines,
        ];

        // The app may prefer one card per challan, with its products under it.
        if ($request->input('group_by') === 'challan') {
            $result['challans'] = $this->groupByChallan($lines);
        }

        return response()->json(apiSuccessResponse('Fetched successfully', $result), 200);
    }

    /** How many lines are waiting and how many have been confirmed. */
    private function counts($userId)
    {
        $base = SamplingDispatch::forEmployee($userId);

        $pending  = (clone $base)->notReceived()->count();
        $received = (clone $base)->received()->count();

        return [
            'pending'  => $pending,
            'received' => $received,
            'total'    => $pending + $received,
        ];
    }

    /** Lines sharing a dispatch_ref_no went out together on one challan. */
    private function groupByChallan($lines)
    {
        return $lines->groupBy('dispatch_ref_no')->map(function ($rows, $ref) {
            $first = $rows->first();

            return [
                'dispatch_ref_no' => $ref,
                'dispatch_date'   => $first['dispatch_date'],
                'invoice_no'      => $first['invoice_no'],
                'invoice_date'    => $first['invoice_date'],
                'transport'       => $first['transport'],
                'lr_no'           => $first['lr_no'],
                'total_qty'       => round($rows->sum('dispatch_qty'), 3),
                'lines'           => $rows->values(),
                'pending_lines'   => $rows->where('is_received', false)->count(),
            ];
        })->values();
    }

    // ══════════════════════════════════════════════════════════════════
    //  RECEIPT
    // ══════════════════════════════════════════════════════════════════

    /**
     * Confirm that dispatched material arrived.
     *
     * Post the ids of the lines being confirmed; the whole post is one
     * transaction, so either every line is confirmed or none is.
     */
    public function markReceived(Request $request)
    {
        if (!$this->authenticated()) {
            return response()->json(apiErrorResponse('Invalid user'), 422);
        }

        $validator = Validator::make($request->all(), [
            'dispatch_ids'   => 'required|array|min:1',
            'dispatch_ids.*' => 'required|integer|distinct|exists:sampling_dispatches,id',
            'received_at'    => 'nullable|date',
        ], [
            'dispatch_ids.required' => 'Please select the material you have received.',
            'dispatch_ids.*.exists' => 'One of the selected dispatches no longer exists.',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        try {
            $outcome = app(SamplingDispatchService::class)->markReceived(
                $request->input('dispatch_ids'),
                $this->resp['user']['id'],
                $request->input('received_at')
            );
        } catch (ValidationException $e) {
            return response()->json(apiErrorResponse(implode(' ', array_map(function ($messages) {
                return is_array($messages) ? implode(' ', $messages) : $messages;
            }, $e->errors()))), 422);
        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }

        return response()->json(apiSuccessResponse('Material marked received successfully', [
            'received'         => $outcome['received'],
            'already_received' => $outcome['already_received'],
            'counts'           => $this->counts($this->resp['user']['id']),
            'dispatches'       => $outcome['dispatches']->map(function ($d) {
                return $d->fresh(['product', 'sampling_request'])->toApiArray();
            })->values(),
        ]), 200);
    }

    private function authenticated()
    {
        return !empty($this->resp['status']) && isset($this->resp['user']);
    }
}
