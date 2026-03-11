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
     */
    public function index(Request $request)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        // Ensure we handle leading zeros or string inputs from request
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $expenses = UserExpense::with('category')
            ->where('user_id', $this->resp['user']['id'])
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->orderBy('expense_date', 'desc')
            ->get();

        return response()->json(apiSuccessResponse('Expenses fetched', [
            'month' => (int)$month,
            'year' => (int)$year,
            'expenses' => $expenses
        ]), 200);
    }

    /**
     * Save a single expense
     */
    public function save(Request $request)
    {
        if (!$this->resp['status']) return response()->json(apiErrorResponse('Unauthorized'), 401);

        $rules = [
            'category_id'      => 'required|exists:expense_categories,id',
            'expense_date'     => 'required|date',
            'requested_amount' => 'required|numeric',
            'status' => 'required',
            'image'            => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        try {
            DB::beginTransaction();
            
            $category = ExpenseCategory::find($request->category_id);
            $userId = $this->resp['user']['id'];
            
            $expense = new UserExpense();
            $expense->user_id = $userId;
            $expense->category_id = $request->category_id;
            $expense->expense_date = $request->expense_date;
            $expense->missed_entry = $request->missed_entry ?? 0;
            $expense->remarks = $request->remarks;
            $expense->status = $request->status;

            // Special Logic for Distance Travelled
            if ($category->is_travel) {
                $expense->travel_km = $request->travel_km;
                $expense->charge_per_km = $request->charge_per_km;
                $expense->is_intercity = $request->is_intercity ?? 0;
                $expense->intercity_route = $request->intercity_route;
                // Auto-calculate amount to prevent frontend tampering
                $expense->requested_amount = $request->travel_km * $request->charge_per_km;
            } else {
                $expense->requested_amount = $request->requested_amount;
            }

            // Image Upload Logic
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destinationPath = public_path('ExpenseReceipts/' . $userId . '/');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $fileName = 'exp_' . uniqid() . '_' . date('His') . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);
                $expense->image = $fileName;
            }

            $expense->save();
            DB::commit();

            return response()->json(apiSuccessResponse('Expense submitted successfully', ['expense' => $expense]), 200);

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

        // Fetch category to check is_travel status
        $category = ExpenseCategory::find($expense->category_id);

        // Dynamic Validation Rules
        $rules = [
            'expense_date' => 'required|date',
            'status'       => 'required',
            'remarks'      => 'required|string',
            'image'        => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120'
        ];

        // If it's a travel category (is_travel = 1), travel fields are mandatory
        if ($category->is_travel) {
            $rules['travel_km'] = 'required|numeric';
            $rules['charge_per_km'] = 'required|numeric';
        } else {
            $rules['requested_amount'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        try {
            DB::beginTransaction();

            $expense->expense_date = $request->expense_date;
            $expense->remarks = $request->remarks;
            $expense->status = $request->status;

            if ($category->is_travel) {
                $expense->travel_km = $request->travel_km;
                $expense->charge_per_km = $request->charge_per_km;
                $expense->is_intercity = $request->is_intercity ?? 0;
                $expense->intercity_route = $request->intercity_route;
                // Recalculate amount
                $expense->requested_amount = $request->travel_km * $request->charge_per_km;
            } else {
                $expense->requested_amount = $request->requested_amount;
            }

            // Handle Image Update (Optional: Delete old image if needed)
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $userId = $this->resp['user']['id'];
                $destinationPath = public_path('ExpenseReceipts/' . $userId . '/');

                $fileName = 'exp_upd_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);
                $expense->image = $fileName;
            }

            $expense->save();
            DB::commit();

            return response()->json(apiSuccessResponse('Expense updated successfully', ['expense' => $expense]), 200);

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

        // Note: You might want to delete the physical image file from storage here as well
        $expense->delete();

        return response()->json(apiSuccessResponse('Expense deleted successfully'), 200);
    }
}