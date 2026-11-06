<?php

namespace App\Http\Controllers\api\Dealers;

use App\AuthToken;
use App\Customer;
use App\Dealer;
use App\Http\Controllers\Controller;
use App\MonthlySale;
use App\MonthlySaleProduct;
use DB;
use Illuminate\Http\Request;
use Validator;

/**
 * Monthly Sale — the dealer records what each customer bought in a month.
 * Dealer app only (DealerAuth). There is no admin screen yet.
 *
 *   index()   GET|POST /api/dealer/monthly-sales
 *       List. Optional from_month / to_month as "MM-YYYY" (both inclusive),
 *       e.g. 09-2026 to 11-2026 = September to November. Defaults to the
 *       current month. Optional customer_id filter.
 *
 *   store()   POST /api/dealer/add-monthly-sale
 *   update()  POST /api/dealer/edit-monthly-sale    (monthly_sale_id)
 *       Body: month, year, customer_id, remarks,
 *       products: [{product_id, qty}]  (qty up to 3 decimal places).
 *       dealer_id is not posted. Same as the purchase projection API:
 *       dealer_id            = the main dealer (Dealer::getParentDealer),
 *       created_by_dealer_id = the login that added it (main dealer or add-on user).
 *       Both are set on add only; an edit keeps them as they were.
 *       On edit the product list sent replaces the stored one.
 *
 *   destroy() POST /api/dealer/delete-monthly-sale  (monthly_sale_id)
 *
 * A dealer sees and changes the sales of its own dealer group only (the
 * parent dealer and its sub-dealers / add-on users), the same scope the
 * dealer customers list uses.
 */
class MonthlySaleController extends Controller
{
    /** Allows up to three decimal places, e.g. 12, 12.5, 12.125. */
    const QTY_REGEX = '/^\d+(\.\d{1,3})?$/';

    /** "MM-YYYY", month with or without a leading zero. */
    const MONTH_YEAR_REGEX = '/^(0?[1-9]|1[0-2])-(\d{4})$/';

    /**
     * The logged-in dealer, taken from the Bearer token (Authorization header).
     * This is the only source of the dealer id in this module — it is never read
     * from the request body. Null if the token is missing or invalid.
     */
    private function dealer(Request $request)
    {
        if (!$request->header('Authorization')) {
            return null;
        }
        $resp = AuthToken::verifyUser($request->header('Authorization'));

        return ($resp['status'] && isset($resp['dealer'])) ? $resp['dealer'] : null;
    }

    private function unauthorized()
    {
        return response()->json(apiErrorResponse('Unauthorized or invalid request'), 401);
    }

    // ══════════════════════════════════════════════════════════════════
    //  LIST
    // ══════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $dealer = $this->dealer($request);
        if (!$dealer) {
            return $this->unauthorized();
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'from_month'  => ['nullable', 'regex:' . self::MONTH_YEAR_REGEX],
            'to_month'    => ['nullable', 'regex:' . self::MONTH_YEAR_REGEX],
            'customer_id' => 'nullable|integer',
        ], [
            'from_month.regex' => 'From month must be in MM-YYYY format, e.g. 09-2026.',
            'to_month.regex'   => 'To month must be in MM-YYYY format, e.g. 11-2026.',
        ]);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        // Default: current month. If only one end is sent, the range is that one month.
        $current = (int) date('Ym');
        $from = !empty($data['from_month']) ? $this->period($data['from_month']) : null;
        $to   = !empty($data['to_month'])   ? $this->period($data['to_month'])   : null;
        $from = $from ?: ($to ?: $current);
        $to   = $to   ?: $from;

        if ($from > $to) {
            return response()->json(apiErrorResponse('From month cannot be after to month.'), 422);
        }

        $dealerIds = Dealer::getParentChildDealers($dealer);

        $query = MonthlySale::with(['dealer', 'customer', 'created_by_dealer', 'products.product'])
            ->whereIn('dealer_id', $dealerIds)
            ->betweenPeriods($from, $to);

        if (!empty($data['customer_id'])) {
            $query->where('customer_id', $data['customer_id']);
        }

        $sales = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc')->get();

        return response()->json(apiSuccessResponse('Monthly sales fetched successfully', [
            'from_month'    => $this->formatPeriod($from),
            'to_month'      => $this->formatPeriod($to),
            'total_qty'     => round($sales->sum(function ($sale) { return $sale->products->sum('qty'); }), 3),
            'monthly_sales' => $sales->map->toApiArray()->values(),
        ]), 200);
    }

    // ══════════════════════════════════════════════════════════════════
    //  ADD / EDIT / DELETE
    // ══════════════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $dealer = $this->dealer($request);
        if (!$dealer) {
            return $this->unauthorized();
        }

        return $this->save($request, $dealer, new MonthlySale);
    }

    public function update(Request $request)
    {
        $dealer = $this->dealer($request);
        if (!$dealer) {
            return $this->unauthorized();
        }

        $sale = $this->findOwnSale($request->input('monthly_sale_id'), $dealer);
        if (!$sale) {
            return response()->json(apiErrorResponse('Monthly sale not found.'), 422);
        }

        return $this->save($request, $dealer, $sale);
    }

    public function destroy(Request $request)
    {
        $dealer = $this->dealer($request);
        if (!$dealer) {
            return $this->unauthorized();
        }

        $sale = $this->findOwnSale($request->input('monthly_sale_id'), $dealer);
        if (!$sale) {
            return response()->json(apiErrorResponse('Monthly sale not found.'), 422);
        }

        DB::transaction(function () use ($sale) {
            $sale->products()->delete();
            $sale->delete();
        });

        return response()->json(apiSuccessResponse('Monthly sale deleted successfully'), 200);
    }

    // ══════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════

    /** Shared by add and edit so the two can never validate differently. */
    private function save(Request $request, array $dealer, MonthlySale $sale)
    {
        $data = $request->all();

        // The app may post products as a JSON string (form-data); the spec also called it multiple_products.
        $products = $data['products'] ?? ($data['multiple_products'] ?? null);
        if (is_string($products)) {
            $products = json_decode($products, true);
        }
        $data['products'] = $products;

        $dealerIds = Dealer::getParentChildDealers($dealer);

        $validator = Validator::make($data, [
            'month'                => 'required|integer|between:1,12',
            'year'                 => 'required|integer|between:2000,2100',
            'customer_id'          => 'required|integer|exists:customers,id',
            'remarks'              => 'nullable|string',
            'products'             => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|distinct|exists:products,id',
            'products.*.qty'       => ['required', 'numeric', 'gt:0', 'regex:' . self::QTY_REGEX],
        ], [
            'products.required'           => 'Please add at least one product.',
            'products.*.product_id.distinct' => 'The same product has been added more than once.',
            'products.*.qty.regex'        => 'Quantity can have at most 3 decimal places.',
            'products.*.qty.gt'           => 'Quantity must be greater than 0.',
        ]);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        // The customer must belong to this dealer group.
        $customerOk = Customer::where('id', $data['customer_id'])->whereIn('dealer_id', $dealerIds)->exists();
        if (!$customerOk) {
            return response()->json(apiErrorResponse('This customer is not linked to your dealership.'), 422);
        }

        $isNew = !$sale->exists;

        DB::transaction(function () use ($sale, $data, $dealer, $isNew) {
            $sale->month       = (int) $data['month'];
            $sale->year        = (int) $data['year'];
            $sale->customer_id = $data['customer_id'];
            $sale->remarks     = $data['remarks'] ?? null;
            if ($isNew) {
                // From the token, never the request: the parent dealer, as savePurchaseProjection does.
                $sale->dealer_id  = Dealer::getParentDealer($dealer);
                $sale->created_by_dealer_id = $dealer['id'];
            }
            $sale->save();

            // The list sent replaces the stored one.
            $sale->products()->delete();
            foreach ($data['products'] as $product) {
                MonthlySaleProduct::create([
                    'monthly_sale_id' => $sale->id,
                    'product_id'      => $product['product_id'],
                    'qty'             => round((float) $product['qty'], 3),
                ]);
            }
        });

        $sale->load(['dealer', 'customer', 'created_by_dealer', 'products.product']);

        return response()->json(apiSuccessResponse(
            $isNew ? 'Monthly sale added successfully' : 'Monthly sale updated successfully',
            ['monthly_sale' => $sale->toApiArray()]
        ), 200);
    }

    /** A sale the logged-in dealer's group owns, or null. */
    private function findOwnSale($id, array $dealer)
    {
        if (empty($id)) {
            return null;
        }

        return MonthlySale::where('id', $id)
            ->whereIn('dealer_id', Dealer::getParentChildDealers($dealer))
            ->first();
    }

    /** "09-2026" → 202609 */
    private function period($monthYear)
    {
        preg_match(self::MONTH_YEAR_REGEX, $monthYear, $m);

        return ((int) $m[2]) * 100 + (int) $m[1];
    }

    /** 202609 → "09-2026" */
    private function formatPeriod($period)
    {
        return sprintf('%02d-%04d', $period % 100, intdiv($period, 100));
    }
}
