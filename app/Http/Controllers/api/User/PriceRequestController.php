<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\Customer;
use App\CustomerPriceRequest;
use Validator;
use DB;

/**
 * Class PriceRequestController
 *
 * Executive-side APIs for raising customer price requests.
 * Auth via custom token, same pattern as DvrController.
 *
 * Endpoints (see routes snippet in README):
 *   GET  /api/user/price-requests            → list own requests (filters: status, customer_id, month, year)
 *   POST /api/user/price-requests            → create (status defaults to Pending)
 *   GET  /api/user/price-requests/meta       → DP / premium map / packing costs / customer terms
 *                                              so the app can compute the viability live
 */
class PriceRequestController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    protected function authorized()
    {
        return !empty($this->resp['status']) && isset($this->resp['user']);
    }

    /**
     * 1️⃣ LIST OWN PRICE REQUESTS
     * GET /api/user/price-requests?status=Pending&customer_id=&month=&year=
     */
    public function index(Request $request)
    {
        if (!$this->authorized()) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $query = CustomerPriceRequest::with([
                'customer:id,name,business_model,payment_term,freight_basis,freight',
                'product:id,product_name',
                'action_user:id,name',
            ])
            ->where('user_id', $this->resp['user']['id']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('created_at', $request->month)
                  ->whereYear('created_at', $request->year);
        }

        $requests = $query->orderBy('id', 'DESC')->get();

        return response()->json(
            apiSuccessResponse('Price requests fetched', ['price_requests' => $requests]),
            200
        );
    }

    /**
     * 2️⃣ CREATE PRICE REQUEST (status = Pending)
     * POST /api/user/price-requests
     */
    public function store(Request $request)
    {
        if (!$this->authorized()) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'customer_id'            => 'bail|required|exists:customers,id',
            'product_id'             => 'bail|required|exists:products,id',
            'payment_term'           => 'bail|required|in:' . implode(',', payment_terms()),
            'freight_basis'          => 'bail|required|in:Paid by Company,Paid by Customer',
            'freight'                => 'bail|nullable|numeric|min:0',
            'packing_size'           => 'bail|required|in:' . implode(',', array_keys(packing_sizes())),
            'final_msp'              => 'bail|required|numeric|min:0',
            'final_customer_price'   => 'bail|required|numeric|gt:0',
            'selling_expense_basis'  => 'bail|nullable|in:%,Rs/kg',
            'selling_expense_value'  => 'bail|required|numeric|min:0',   // Selling Expenses / ORC %
            'selling_expenses'       => 'bail|required|numeric|min:0',   // Rs./kg
            'additional_realization' => 'bail|required|numeric',          // may be negative
        ];

        // Freight must be > 0 when the company pays it — same rule as the admin screen
        if ($request->input('freight_basis') == 'Paid by Company') {
            $rules['freight'] = 'bail|required|numeric|gt:0';
        }

        $validator = Validator::make($request->all(), $rules, [
            'freight.gt' => 'Freight must be greater than 0 when paid by company.',
        ]);

        if (!$validator->passes()) {
            return response()->json(apiErrorResponse($validator->messages()->first()), 422);
        }

        // Price requests only make sense for models that buy directly
        $customer = Customer::find($request->customer_id);
        if (!in_array($customer->business_model, ['Direct Customer', 'Hybrid'])) {
            return response()->json(
                apiErrorResponse('Price requests are only allowed for Direct Customer / Hybrid customers.'),
                422
            );
        }

        // Avoid duplicate open requests for the same customer + product
        $open = CustomerPriceRequest::where('customer_id', $request->customer_id)
            ->where('product_id', $request->product_id)
            ->where('status', 'Pending')
            ->exists();
        if ($open) {
            return response()->json(
                apiErrorResponse('A pending price request already exists for this customer & product.'),
                422
            );
        }

        $pr = CustomerPriceRequest::create([
            'user_id'                => $this->resp['user']['id'],
            'customer_id'            => $request->customer_id,
            'product_id'             => $request->product_id,
            'payment_term'           => $request->payment_term,
            'freight_basis'          => $request->freight_basis,
            'freight'                => ($request->freight_basis == 'Paid by Company') ? $request->freight : 0,
            'packing_size'           => $request->packing_size,
            'final_msp'              => $request->final_msp,
            'final_customer_price'   => $request->final_customer_price,
            'selling_expense_basis'  => $request->input('selling_expense_basis', '%'),
            'selling_expense_value'  => $request->selling_expense_value,
            'selling_expenses'       => $request->selling_expenses,
            'additional_realization' => $request->additional_realization,
            'status'                 => 'Pending',
        ]);

        return response()->json(
            apiSuccessResponse('Price request submitted for approval', ['price_request' => $pr]),
            200
        );
    }

    /**
     * 3️⃣ META FOR LIVE VIABILITY IN THE APP
     * GET /api/user/price-requests/meta?product_id=&customer_id=
     *
     * Returns Standard DP (latest price effective till today), premium map,
     * packing costs, standard packing label, and the customer's saved
     * payment/freight terms so the app can mirror the admin screen's math.
     */
    public function meta(Request $request)
    {
        if (!$this->authorized()) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $data = [
            'payment_terms' => payment_terms(),
            'premium_map'   => collect(payment_terms())->mapWithKeys(function ($t) {
                return [$t => direct_sales_premium($t)];
            }),
            'packing_sizes' => packing_sizes(),
            'packing_costs' => collect(array_keys(packing_sizes()))->mapWithKeys(function ($p) {
                return [$p => additional_packing_cost($p)];
            }),
        ];

        if ($request->filled('product_id')) {
            $data['standard_dp']      = getProductStandardDp($request->product_id);
            $data['standard_packing'] = getProductStandardPacking($request->product_id);
        }

        if ($request->filled('customer_id')) {
            $customer = Customer::select('id', 'name', 'business_model', 'payment_term', 'freight_basis', 'freight')
                ->find($request->customer_id);
            $data['customer'] = $customer;
        }

        return response()->json(apiSuccessResponse('Meta fetched', $data), 200);
    }
}
