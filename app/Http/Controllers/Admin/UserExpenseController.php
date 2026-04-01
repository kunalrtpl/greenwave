<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
class UserExpenseController extends Controller
{
    /**
     * Display a listing of user expenses with filters.
     */
    public function index(Request $request)
    {
        Session::put('active','expenses');
        $query = DB::table('user_expenses as ue')
            ->join('expense_categories as ec', 'ue.category_id', '=', 'ec.id')
            ->leftJoin('users as u',  'ue.user_id',    '=', 'u.id')
            ->leftJoin('users as vb', 'ue.verified_by','=', 'vb.id')
            ->leftJoin('users as ab', 'ue.approved_by','=', 'ab.id')
            ->select(
                'ue.id',
                'ue.user_id',
                'ue.expense_date',
                'ue.missed_entry',
                'ue.missed_entry_reason',
                'ue.requested_amount',
                'ue.approved_amount',
                'ue.travel_km',
                'ue.charge_per_km',
                'ue.is_intercity',
                'ue.intercity_route',
                'ue.remarks',
                'ue.image',
                'ue.alternative_image',
                'ue.status',
                'ue.internal_remarks',
                'ue.admin_remarks',
                'ue.created_at',
                'ue.updated_at',
                'ue.verified_by',
                'ec.id as category_id',
                'ec.name as category_name',
                'ec.is_travel',
                'u.name as employee_name',
                'u.mobile as employee_mobile',
                'vb.name as verified_by_name',
                'ab.name as approved_by_name'
            )
            ->orderBy('ue.id', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('ue.user_id', $request->employee_id);
        }
        if ($request->filled('month')) {
            $query->whereMonth('ue.expense_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('ue.expense_date', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('ue.status', $request->status);
        }
        // Verification filter: 'yes' = verified_by is not null, 'no' = verified_by is null
        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('ue.verified_by');
            } else {
                $query->whereNull('ue.verified_by');
            }
        }

        $expenses = $query->paginate(30)->appends($request->except('page'));

        // Batch-load UNREAD query counts (admin_read = 0) per expense
        $expenseIds = $expenses->pluck('id')->toArray();
        $queryCounts       = [];  // total messages
        $unreadQueryCounts = [];  // unread by admin
        if (!empty($expenseIds)) {
            $queryCounts = DB::table('user_expenses_queries')
                ->whereIn('expense_id', $expenseIds)
                ->select('expense_id', DB::raw('count(*) as total'))
                ->groupBy('expense_id')
                ->pluck('total', 'expense_id')
                ->toArray();

            $unreadQueryCounts = DB::table('user_expenses_queries')
                ->whereIn('expense_id', $expenseIds)
                ->where('admin_read', 0)
                ->where('sender_type', 'employee') // only employee messages are "unread" for admin
                ->select('expense_id', DB::raw('count(*) as total'))
                ->groupBy('expense_id')
                ->pluck('total', 'expense_id')
                ->toArray();
        }

        $employees = DB::table('users')
            ->select('id', 'name', 'mobile')
            ->where('status', 1)
            ->where('type', 'employee')
            ->orderBy('name')
            ->get();

        $years = [];
        for ($y = date('Y'); $y >= date('Y') - 4; $y--) {
            $years[] = $y;
        }

        $title = 'User Expenses';
        return view('admin.user_expenses.index',
            compact('expenses', 'employees', 'years', 'title', 'queryCounts', 'unreadQueryCounts'));
    }

    /**
     * Update expense status via AJAX.
     * Now also saves admin_remarks.
     */
    public function updateStatus(Request $request, $id)
    {
        $expense = DB::table('user_expenses')->where('id', $id)->first();
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found.'], 404);
        }

        $requestedAmount = (float) $expense->requested_amount;

        $request->validate([
            'status' => 'required|in:Approved,Partially Approved,Rejected',
            'approved_amount' => [
                'required_if:status,Partially Approved',
                'nullable',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($requestedAmount) {
                    if (!is_null($value) && (float) $value > $requestedAmount) {
                        $fail(
                            'Approved amount (' . number_format((float)$value, 2) . ') ' .
                            'cannot exceed the requested amount of ' .
                            number_format($requestedAmount, 2) . '.'
                        );
                    }
                },
            ],
            'admin_remarks' => 'nullable|string|max:1000',
        ]);

        $data = [
            'status'        => $request->status,
            'admin_remarks' => $request->admin_remarks ?? null,
            'updated_at'    => now(),
        ];

        if ($request->status === 'Approved') {
            $data['approved_amount'] = $expense->requested_amount;
            $data['approved_by']     = auth()->id();
        } elseif ($request->status === 'Partially Approved') {
            $data['approved_amount'] = $request->approved_amount;
            $data['approved_by']     = auth()->id();
        } elseif ($request->status === 'Rejected') {
            $data['approved_amount'] = 0;
            $data['approved_by']     = auth()->id();
        }

        DB::table('user_expenses')->where('id', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    /**
     * Toggle verified (YES/NO) via AJAX — no remarks, instant toggle.
     */
    public function toggleVerified(Request $request, $id)
    {
        $expense = DB::table('user_expenses')->where('id', $id)->first();
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found.'], 404);
        }

        if ($expense->verified_by) {
            // Un-verify — keep existing internal_remarks intact (do NOT clear them)
            DB::table('user_expenses')->where('id', $id)->update([
                'verified_by' => null,
                'updated_at'  => now(),
            ]);
            $verified = false;
        } else {
            // Verify
            DB::table('user_expenses')->where('id', $id)->update([
                'verified_by' => auth()->id(),
                'updated_at'  => now(),
            ]);
            $verified = true;
        }

        // Reload to return fresh internal_remarks & verified_by_name
        $updated = DB::table('user_expenses as ue')
            ->leftJoin('users as vb', 'ue.verified_by', '=', 'vb.id')
            ->where('ue.id', $id)
            ->select('ue.internal_remarks', 'vb.name as verified_by_name')
            ->first();

        return response()->json([
            'success'          => true,
            'verified'         => $verified,
            'internal_remarks' => $updated->internal_remarks ?? null,
            'verified_by_name' => $verified ? (auth()->user()->name ?? 'Admin') : null,
        ]);
    }

    /**
     * Save / update internal remarks for an expense (AJAX).
     * Separate from verify — can be called any time.
     */
    public function saveInternalRemarks(Request $request, $id)
    {
        $expense = DB::table('user_expenses')->where('id', $id)->first();
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found.'], 404);
        }

        $request->validate([
            'internal_remarks' => 'nullable|string|max:1000',
        ]);

        DB::table('user_expenses')->where('id', $id)->update([
            'internal_remarks' => $request->internal_remarks ?? null,
            'updated_at'       => now(),
        ]);

        return response()->json([
            'success'          => true,
            'internal_remarks' => $request->internal_remarks ?? null,
        ]);
    }

    /**
     * Get all queries for an expense (AJAX).
     * Also marks all employee messages as admin_read = 1.
     */
    public function getQueries($id)
    {
        // Mark all employee messages as read by admin
        DB::table('user_expenses_queries')
            ->where('expense_id', $id)
            ->where('sender_type', 'employee')
            ->where('admin_read', 0)
            ->update(['admin_read' => 1]);

        $queries = DB::table('user_expenses_queries as q')
            ->leftJoin('users as u', 'q.sender_id', '=', 'u.id')
            ->where('q.expense_id', $id)
            ->select(
                'q.id',
                'q.message',
                'q.sender_type',
                'q.admin_read',
                'q.user_read',
                'q.created_at',
                'u.name as sender_name'
            )
            ->orderBy('q.created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'queries' => $queries,
            'total'   => $queries->count(),
        ]);
    }

    /**
     * Raise / post a new query from admin panel (AJAX).
     * Admin messages start with user_read = 0 (unread by employee).
     */
    public function raiseQuery(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $expense = DB::table('user_expenses')->where('id', $id)->first();
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found.'], 404);
        }

        $queryId = DB::table('user_expenses_queries')->insertGetId([
            'expense_id'  => $id,
            'sender_id'   => auth()->id(),
            'sender_type' => 'admin',
            'message'     => trim($request->message),
            'admin_read'  => 1, // admin wrote it — already "read" by admin
            'user_read'   => 0, // employee hasn't read it yet
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $newQuery = DB::table('user_expenses_queries as q')
            ->leftJoin('users as u', 'q.sender_id', '=', 'u.id')
            ->where('q.id', $queryId)
            ->select('q.id', 'q.message', 'q.sender_type', 'q.admin_read', 'q.user_read', 'q.created_at', 'u.name as sender_name')
            ->first();

        $total = DB::table('user_expenses_queries')->where('expense_id', $id)->count();

        // Unread count still pending from employee after this send
        $unread = DB::table('user_expenses_queries')
            ->where('expense_id', $id)
            ->where('sender_type', 'employee')
            ->where('admin_read', 0)
            ->count();

        return response()->json([
            'success' => true,
            'query'   => $newQuery,
            'total'   => $total,
            'unread'  => $unread,
        ]);
    }

    /**
     * Get DVR visits for a user on a specific date (for Distance Travelled expenses)
     */
    public function getVisits(Request $request)
    {
        $userId = $request->input('user_id');
        $date   = $request->input('date');

        if (!$userId || !$date) {
            return response()->json(['success' => false, 'message' => 'Missing parameters.']);
        }

        $visits = \App\UserDvr::where('user_id', $userId)
            ->where('dvr_date', $date)
            ->orderBy('start_time', 'asc')
            ->get();

        $result = [];
        foreach ($visits as $visit) {
            $customerName    = null;
            $customerAddress = null;
            $crrName         = null;
            $crrAddress      = null;

            // Try to get customer info
            if ($visit->customer_id) {
                $customer = \App\Customer::find($visit->customer_id);
                if ($customer) {
                    $customerName    = $customer->name;
                    $customerAddress = $customer->address ?? null;
                }
            }

            // Try customer register request as fallback
            if (!$customerName && $visit->customer_register_request_id) {
                $crr = \App\CustomerRegisterRequest::find($visit->customer_register_request_id);
                if ($crr) {
                    $crrName    = $crr->name;
                    $crrAddress = $crr->address ?? null;
                }
            }

            $result[] = [
                'id'              => $visit->id,
                'customer_name'   => $customerName,
                'customer_address'=> $customerAddress,
                'crr_name'        => $crrName,
                'crr_address'     => $crrAddress,
                'start_time'      => $visit->start_time,
                'end_time'        => $visit->end_time,
                'start_location'  => $visit->start_location,
                'end_location'    => $visit->end_location,
                'purpose_of_visit'=> $visit->purpose_of_visit,
                'visit_type'      => $visit->visit_type,
                'remarks'         => $visit->remarks,
            ];
        }

        return response()->json(['success' => true, 'visits' => $result]);
    }
}