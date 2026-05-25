<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Session;

class UserExpenseController extends Controller
{
    /**
     * Display a listing of user expenses with filters.
     */
    public function index(Request $request)
    {
        Session::put('active', 'expenses');

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
            ->orderBy('ue.expense_date', 'desc');

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
        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('ue.verified_by');
            } else {
                $query->whereNull('ue.verified_by');
            }
        }

        $expenses = $query->paginate(30)->appends($request->except('page'));

        // Batch-load query counts per expense
        $expenseIds        = $expenses->pluck('id')->toArray();
        $queryCounts       = [];
        $unreadQueryCounts = [];
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
                ->where('sender_type', 'employee')
                ->select('expense_id', DB::raw('count(*) as total'))
                ->groupBy('expense_id')
                ->pluck('total', 'expense_id')
                ->toArray();
        }

        $employees = DB::table('users')
            ->select('id', 'name', 'mobile')
            ->where('status', 1)
            ->where('type', 'employee')
            ->whereRaw("FIND_IN_SET('travel_n_expenses', app_roles)")
            ->orderBy('name')
            ->get();

        $years = [];
        for ($y = date('Y'); $y >= date('Y') - 4; $y--) {
            $years[] = $y;
        }

        // Visit counts
        $visitCounts = [];
        $expenseKeys = $expenses->map(function ($exp) {
            return ['user_id' => $exp->user_id, 'date' => $exp->expense_date];
        });

        $visitsData = DB::table('user_dvrs')
            ->select('user_id', 'dvr_date', DB::raw('COUNT(*) as total'))
            ->where(function ($q) use ($expenseKeys) {
                foreach ($expenseKeys as $key) {
                    $q->orWhere(function ($sub) use ($key) {
                        $sub->where('user_id', $key['user_id'])
                            ->whereDate('dvr_date', $key['date']);
                    });
                }
            })
            ->groupBy('user_id', 'dvr_date')
            ->get();

        foreach ($visitsData as $v) {
            $visitCounts[$v->user_id . '_' . $v->dvr_date] = $v->total;
        }

        $title = 'User Expenses';
        return view('admin.user_expenses.index',
            compact('expenses', 'employees', 'years', 'title', 'queryCounts', 'unreadQueryCounts', 'visitCounts'));
    }

    /**
     * Export filtered expenses as a beautifully formatted PDF (dompdf).
     */
    public function exportPdf(Request $request)
    {
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
                'ue.status',
                'ue.internal_remarks',
                'ue.admin_remarks',
                'ue.created_at',
                'ue.verified_by',
                'ec.name as category_name',
                'ec.is_travel',
                'u.name as employee_name',
                'u.mobile as employee_mobile',
                'vb.name as verified_by_name',
                'ab.name as approved_by_name'
            )
            ->orderBy('ue.expense_date', 'ASC');

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
        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('ue.verified_by');
            } else {
                $query->whereNull('ue.verified_by');
            }
        }

        $expenses = $query->get();

        $employee = null;
        if ($request->filled('employee_id')) {
            $employee = DB::table('users')
                ->select('id', 'name', 'mobile')
                ->where('id', $request->employee_id)
                ->first();
        }

        $data = [
            'expenses'       => $expenses,
            'employee'       => $employee,
            'filterMonth'    => $request->month    ?? null,
            'filterYear'     => $request->year     ?? null,
            'filterStatus'   => $request->status   ?? null,
            'filterVerified' => $request->verified ?? null,
        ];

        $pdf = Pdf::loadView('admin.user_expenses.expense_report_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled'         => false,
                'dpi'                  => 120,
            ]);

        $filename = 'expense-report';
        if ($employee) {
            $filename .= '-' . \Illuminate\Support\Str::slug($employee->name);
        }
        if ($request->filled('month') && $request->filled('year')) {
            $filename .= '-' . date('M', mktime(0, 0, 0, $request->month, 1)) . '-' . $request->year;
        } elseif ($request->filled('year')) {
            $filename .= '-' . $request->year;
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Update expense status via AJAX.
     */
    public function updateStatus(Request $request, $id)
    {
        $expense = DB::table('user_expenses')->where('id', $id)->first();
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found.'], 404);
        }

        $requestedAmount = (float) $expense->requested_amount;

        $request->validate([
            'status'         => 'required|in:Approved,Partially Approved,Rejected',
            'approved_amount' => [
                'required_if:status,Partially Approved',
                'nullable',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($requestedAmount) {
                    if (!is_null($value) && (float) $value > $requestedAmount) {
                        $fail(
                            'Approved amount (' . number_format((float) $value, 2) . ') ' .
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
     * Toggle verified (YES/NO) via AJAX.
     */
    public function toggleVerified(Request $request, $id)
    {
        $expense = DB::table('user_expenses')->where('id', $id)->first();
        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found.'], 404);
        }

        if ($expense->verified_by) {
            DB::table('user_expenses')->where('id', $id)->update([
                'verified_by' => null,
                'updated_at'  => now(),
            ]);
            $verified = false;
        } else {
            DB::table('user_expenses')->where('id', $id)->update([
                'verified_by' => auth()->id(),
                'updated_at'  => now(),
            ]);
            $verified = true;
        }

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
     */
    public function getQueries($id)
    {
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
     * Post a new query from admin panel (AJAX).
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
            'admin_read'  => 1,
            'user_read'   => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $newQuery = DB::table('user_expenses_queries as q')
            ->leftJoin('users as u', 'q.sender_id', '=', 'u.id')
            ->where('q.id', $queryId)
            ->select('q.id', 'q.message', 'q.sender_type', 'q.admin_read', 'q.user_read', 'q.created_at', 'u.name as sender_name')
            ->first();

        $total = DB::table('user_expenses_queries')->where('expense_id', $id)->count();

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
     * Get DVR visits for a user on a specific date (AJAX).
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

            if ($visit->customer_id) {
                $customer = \App\Customer::find($visit->customer_id);
                if ($customer) {
                    $customerName    = $customer->name;
                    $customerAddress = $customer->address ?? null;
                }
            }

            if (!$customerName && $visit->customer_register_request_id) {
                $crr = \App\CustomerRegisterRequest::find($visit->customer_register_request_id);
                if ($crr) {
                    $crrName    = $crr->name;
                    $crrAddress = $crr->address ?? null;
                }
            }

            $result[] = [
                'id'               => $visit->id,
                'customer_name'    => $customerName,
                'customer_address' => $customerAddress,
                'crr_name'         => $crrName,
                'crr_address'      => $crrAddress,
                'start_time'       => $visit->start_time,
                'end_time'         => $visit->end_time,
                'start_location'   => $visit->start_location,
                'end_location'     => $visit->end_location,
                'purpose_of_visit' => $visit->purpose_of_visit,
                'visit_type'       => $visit->visit_type,
                'remarks'          => $visit->remarks,
            ];
        }

        return response()->json(['success' => true, 'visits' => $result]);
    }
}