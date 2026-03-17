<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\UserExpense;
use App\ExpenseCategory;
use Validator;
use Carbon\Carbon;
use DB;

class ExpenseController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    /**
     * Fetch all categories for the UI
     */
    public function categories()
    {
        $categories = ExpenseCategory::all();
        return response()->json(apiSuccessResponse('Categories fetched', ['categories' => $categories]), 200);
    }

    /**
     * List expenses with Month/Year filter
     * Now includes: category, queries (with sender info), and logged-in user details
     */
    public function index(Request $request)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $userId = $this->resp['user']['id'];
        $month  = $request->input('month', date('m'));
        $year   = $request->input('year', date('Y'));

        // ---- Expenses with category ----
        $expenseModels = UserExpense::with(['category'])
            ->where('user_id', $userId)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->orderBy('expense_date', 'desc')
            ->get();

        // ---- Batch-load all queries for these expenses (avoids N+1) ----
        $expenseIds = $expenseModels->pluck('id')->toArray();

        $allQueries   = [];
        $unreadCounts = [];
        if (!empty($expenseIds)) {
            $rows = DB::table('user_expenses_queries as q')
                ->leftJoin('users as u', 'q.sender_id', '=', 'u.id')
                ->whereIn('q.expense_id', $expenseIds)
                ->select(
                    'q.id',
                    'q.expense_id',
                    'q.message',
                    'q.sender_type',
                    'q.admin_read',
                    'q.user_read',
                    'q.created_at',
                    'u.id    as sender_id',
                    'u.name  as sender_name',
                    'u.image as sender_image'
                )
                ->orderBy('q.created_at', 'asc')
                ->get();

            foreach ($rows as $row) {
                $allQueries[$row->expense_id][] = $row;
            }

            // Batch unread counts — admin messages not yet read by employee
            $unreadRows = DB::table('user_expenses_queries')
                ->whereIn('expense_id', $expenseIds)
                ->where('sender_type', 'admin')
                ->where('user_read', 0)
                ->select('expense_id', DB::raw('count(*) as total'))
                ->groupBy('expense_id')
                ->pluck('total', 'expense_id')
                ->toArray();

            foreach ($expenseIds as $eid) {
                $unreadCounts[$eid] = $unreadRows[$eid] ?? 0;
            }
        }

        // ---- Build final expenses array with queries embedded ----
        // We convert to array first so dynamic properties are included in JSON output.
        // Eloquent silently drops runtime-assigned properties on model instances during
        // serialization unless you use toArray() + merge explicitly.
        $expenses = $expenseModels->map(function ($expense) use ($allQueries, $unreadCounts) {
            $data                  = $expense->toArray();
            $data['queries']       = $allQueries[$expense->id] ?? [];
            $data['queries_count'] = count($data['queries']);
            $data['unread_count']  = $unreadCounts[$expense->id] ?? 0;
            return $data;
        })->values();

        // ---- Logged-in user details (from users table) ----
        $user = DB::table('users')
            ->where('id', $userId)
            ->select(
                'id',
                'name',
                'mobile',
                'email',
                'image',
                'gender',
                'dob',
                'joining_date',
                'joining_type',
                'correspondence_address',
                'permanent_address',
                'status'
            )
            ->first();

        return response()->json(apiSuccessResponse('Expenses fetched', [
            'month'    => (int) $month,
            'year'     => (int) $year,
            'user'     => $user,
            'expenses' => $expenses,
        ]), 200);
    }

    /**
     * Save a single expense
     */
    public function save(Request $request)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $rules = [
            'category_id'       => 'required|exists:expense_categories,id',
            'expense_date'      => 'required|date',
            'requested_amount'  => 'required|numeric',
            'status'            => 'required',
            'image'             => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
            'alternative_image' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        try {
            DB::beginTransaction();

            $category = ExpenseCategory::find($request->category_id);
            $userId   = $this->resp['user']['id'];

            $expense               = new UserExpense();
            $expense->user_id      = $userId;
            $expense->category_id  = $request->category_id;
            $expense->expense_date = $request->expense_date;
            $expense->missed_entry = $request->missed_entry ?? 0;
            $expense->remarks      = $request->remarks;
            $expense->missed_entry_reason  = $request->missed_entry_reason ?? '';
            $expense->status       = "Pending Approval";

            if ($category->is_travel) {
                $expense->travel_km       = $request->travel_km;
                $expense->charge_per_km   = $request->charge_per_km;
                $expense->is_intercity    = $request->is_intercity ?? 0;
                $expense->intercity_route = $request->intercity_route;
                $expense->requested_amount = $request->travel_km * $request->charge_per_km;
            } else {
                $expense->requested_amount = $request->requested_amount;
            }

            // Main image
            if ($request->hasFile('image')) {
                $expense->image = $this->uploadExpenseFile(
                    $request->file('image'), $userId, 'exp_'
                );
            }

            // Alternative image
            if ($request->hasFile('alternative_image')) {
                $expense->alternative_image = $this->uploadExpenseFile(
                    $request->file('alternative_image'), $userId, 'alt_exp_'
                );
            }

            $expense->save();
            DB::commit();

            // Return with category and empty queries array
            $expense->load('category');
            $expense->queries       = [];
            $expense->queries_count = 0;

            return response()->json(apiSuccessResponse('Expense submitted successfully', [
                'expense' => $expense
            ]), 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    /**
     * Update an existing expense
     */
    public function update(Request $request, $id)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $expense = UserExpense::where('id', $id)
            ->where('user_id', $this->resp['user']['id'])
            ->first();

        if (!$expense) return response()->json(apiErrorResponse('Expense not found'), 404);

        $category = ExpenseCategory::find($expense->category_id);

        $rules = [
            'expense_date'      => 'required|date',
            'status'            => 'required',
            'remarks'           => 'required|string',
            'image'             => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
            'alternative_image' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($category->is_travel) {
            $rules['travel_km']      = 'required|numeric';
            $rules['charge_per_km']  = 'required|numeric';
        } else {
            $rules['requested_amount'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        try {
            DB::beginTransaction();

            $userId = $this->resp['user']['id'];

            $expense->expense_date = $request->expense_date;
            $expense->remarks      = $request->remarks;
            $expense->status       = $request->status;

            if ($category->is_travel) {
                $expense->travel_km        = $request->travel_km;
                $expense->charge_per_km    = $request->charge_per_km;
                $expense->is_intercity     = $request->is_intercity ?? 0;
                $expense->intercity_route  = $request->intercity_route;
                $expense->requested_amount = $request->travel_km * $request->charge_per_km;
            } else {
                $expense->requested_amount = $request->requested_amount;
            }

            if ($request->hasFile('image')) {
                $expense->image = $this->uploadExpenseFile(
                    $request->file('image'), $userId, 'exp_upd_'
                );
            }

            if ($request->hasFile('alternative_image')) {
                $expense->alternative_image = $this->uploadExpenseFile(
                    $request->file('alternative_image'), $userId, 'alt_exp_upd_'
                );
            }

            $expense->save();
            DB::commit();

            $expense->load('category');

            return response()->json(apiSuccessResponse('Expense updated successfully', [
                'expense' => $expense
            ]), 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    /**
     * Delete an expense
     */
    public function destroy($id)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $expense = UserExpense::where('id', $id)
            ->where('user_id', $this->resp['user']['id'])
            ->first();

        if (!$expense) return response()->json(apiErrorResponse('Expense not found or already deleted'), 404);

        $expense->delete();

        return response()->json(apiSuccessResponse('Expense deleted successfully'), 200);
    }

    /**
     * Send a query / reply from the app side for a specific expense
     *
     * POST /api/expenses/{id}/query
     * Body: { "message": "your message here" }
     */
    public function sendQuery(Request $request, $id)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $userId = $this->resp['user']['id'];

        // Make sure the expense belongs to this user
        $expense = UserExpense::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$expense) {
            return response()->json(apiErrorResponse('Expense not found'), 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        try {
            $queryId = DB::table('user_expenses_queries')->insertGetId([
                'expense_id'  => $id,
                'sender_id'   => $userId,
                'sender_type' => 'employee',
                'message'     => trim($request->message),
                'admin_read'  => 0, // admin hasn't read yet
                'user_read'   => 1, // employee wrote it — already read by employee
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Return the full query row with sender info
            $query = DB::table('user_expenses_queries as q')
                ->leftJoin('users as u', 'q.sender_id', '=', 'u.id')
                ->where('q.id', $queryId)
                ->select(
                    'q.id',
                    'q.expense_id',
                    'q.message',
                    'q.sender_type',
                    'q.created_at',
                    'u.id   as sender_id',
                    'u.name as sender_name',
                    'u.image as sender_image'
                )
                ->first();

            // Return updated total count for this expense
            $total = DB::table('user_expenses_queries')
                ->where('expense_id', $id)
                ->count();

            return response()->json(apiSuccessResponse('Query sent successfully', [
                'query'        => $query,
                'total_queries' => $total,
            ]), 200);

        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    /**
     * Get all queries for a specific expense (for app chat view)
     *
     * GET /api/expenses/{id}/queries
     */
    public function getQueries($id)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $userId = $this->resp['user']['id'];

        // Ensure expense belongs to this user
        $expense = UserExpense::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$expense) {
            return response()->json(apiErrorResponse('Expense not found'), 404);
        }

        // Mark all admin messages as read by user when they open the chat
        DB::table('user_expenses_queries')
            ->where('expense_id', $id)
            ->where('sender_type', 'admin')
            ->where('user_read', 0)
            ->update(['user_read' => 1]);

        $queries = DB::table('user_expenses_queries as q')
            ->leftJoin('users as u', 'q.sender_id', '=', 'u.id')
            ->where('q.expense_id', $id)
            ->select(
                'q.id',
                'q.expense_id',
                'q.message',
                'q.sender_type',
                'q.admin_read',
                'q.user_read',
                'q.created_at',
                'u.id    as sender_id',
                'u.name  as sender_name',
                'u.image as sender_image'
            )
            ->orderBy('q.created_at', 'asc')
            ->get();

        // Unread count for this user (admin messages not yet read)
        $unread = DB::table('user_expenses_queries')
            ->where('expense_id', $id)
            ->where('sender_type', 'admin')
            ->where('user_read', 0)
            ->count();

        return response()->json(apiSuccessResponse('Queries fetched', [
            'expense_id'    => (int) $id,
            'total_queries' => $queries->count(),
            'unread_count'  => $unread,
            'queries'       => $queries,
        ]), 200);
    }


    /**
     * Mark all admin queries as read by the user (employee).
     * Call this when the user opens the query/chat screen for an expense.
     *
     * POST /api/expenses/{id}/mark-read
     */
    public function markQueriesRead($id)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $userId = $this->resp['user']['id'];

        // Ensure expense belongs to this user
        $expense = UserExpense::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$expense) {
            return response()->json(apiErrorResponse('Expense not found'), 404);
        }

        // Mark all admin-sent messages as read by the user
        $updated = DB::table('user_expenses_queries')
            ->where('expense_id', $id)
            ->where('sender_type', 'admin')
            ->where('user_read', 0)
            ->update([
                'user_read'  => 1,
                'updated_at' => now(),
            ]);

        // Return remaining unread count (should be 0 after this)
        $unread = DB::table('user_expenses_queries')
            ->where('expense_id', $id)
            ->where('sender_type', 'admin')
            ->where('user_read', 0)
            ->count();

        return response()->json(apiSuccessResponse('Marked as read', [
            'expense_id'     => (int) $id,
            'messages_marked' => $updated,
            'unread_count'   => $unread,
        ]), 200);
    }

    /* -------------------------------------------------------
     * PRIVATE HELPERS
     * ------------------------------------------------------- */

    /**
     * Upload an expense file to public/ExpenseReceipts/{userId}/
     * Returns the saved filename.
     */
    private function uploadExpenseFile($file, $userId, $prefix = 'exp_')
    {
        $destinationPath = public_path('ExpenseReceipts/' . $userId . '/');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $fileName = $prefix . uniqid() . '_' . date('His') . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $fileName);

        return $fileName;
    }
}