<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Customer;
use App\CustomerDiscount;
use App\CustomerPriceRequest;
use Validator;
use Session;
use DB;

/**
 * Admin side of Executive Price Requests.
 *
 *  - index()   listing with status filter
 *  - show()    full detail + customer-master comparison
 *  - approve() ⚠ blocks if payment_term / freight_basis / freight in the
 *              customer master are FILLED and MISMATCH the request; if a
 *              master field is EMPTY it is updated from the request. On
 *              success the price lands in customer_discounts (net_products).
 *  - reject()  requires a reason
 */
class CustomerPriceRequestController extends Controller
{
    /**
     * The three fields that must agree between the request and the
     * customer master before approval is allowed.
     * Returns ['mismatches' => [...], 'fill' => ['column' => value, ...]]
     */
    protected function compareWithMaster(CustomerPriceRequest $pr, Customer $customer)
    {
        $mismatches = [];
        $fill       = [];

        // payment_term
        if (trim((string) $customer->payment_term) === '') {
            $fill['payment_term'] = $pr->payment_term;
        } elseif ($customer->payment_term !== $pr->payment_term) {
            $mismatches[] = "Payment Term (master: {$customer->payment_term} · request: {$pr->payment_term})";
        }

        // freight_basis
        if (trim((string) $customer->freight_basis) === '') {
            $fill['freight_basis'] = $pr->freight_basis;
        } elseif ($customer->freight_basis !== $pr->freight_basis) {
            $mismatches[] = "Freight Basis (master: {$customer->freight_basis} · request: {$pr->freight_basis})";
        }

        // freight (numeric — NULL means "empty"; compare with tolerance)
        if ($customer->freight === null || $customer->freight === '') {
            $fill['freight'] = $pr->freight;
        } elseif (abs((float) $customer->freight - (float) $pr->freight) > 0.001) {
            $mismatches[] = "Freight (master: " . (float) $customer->freight . " · request: " . (float) $pr->freight . ")";
        }

        return ['mismatches' => $mismatches, 'fill' => $fill];
    }

    /** LISTING — GET /admin/price-requests?status=Pending */
    public function index(Request $request)
    {
        Session::put('active', 'priceRequests');

        $status = $request->query('status', 'Pending');

        $query = CustomerPriceRequest::with([
            'user:id,name',
            'customer:id,name,business_model',
            'product:id,product_name',
        ]);

        if (in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $query->where('status', $status);
        } else {
            $status = 'All';
        }
        if ($request->filled('customer')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer . '%');
            });
        }

        $counts = CustomerPriceRequest::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')->pluck('c', 'status');

        $requests = $query->orderBy('id', 'DESC')->paginate(25)->appends($request->query());

        $title = "Price Requests";
        return View::make('admin.customer_price_requests.price-requests')
            ->with(compact('title', 'requests', 'status', 'counts'));
    }

    /** DETAIL — GET /admin/price-requests/{id} */
    public function show($id)
    {
        Session::put('active', 'priceRequests');

        $pr = CustomerPriceRequest::with(['user', 'customer', 'product', 'action_user'])->findOrFail($id);
        $customer = $pr->customer;

        // Comparison for the on-screen check table
        $check = $this->compareWithMaster($pr, $customer);

        // Viability recomputed server-side from the single source of truth
        $sellingExpenseBasis = $pr->selling_expense_basis ?: '%';
        $viability = customer_viability_check(
            getProductStandardDp($pr->product_id),
            $pr->payment_term,
            $pr->packing_size,
            $pr->freight_basis,
            $pr->freight,
            $sellingExpenseBasis,
            $pr->selling_expense_value,
            $pr->final_customer_price
        );

        // Existing net_products row for this customer+product (will be replaced on approval)
        $existing = CustomerDiscount::where('customer_id', $pr->customer_id)
            ->where('product_id', $pr->product_id)
            ->where('discount_type', 'net_products')
            ->first();

        $title = "Price Request #PR-" . $pr->id;
        return View::make('admin.customer_price_requests.price-request-detail')
            ->with(compact('title', 'pr', 'customer', 'check', 'viability', 'existing'));
    }

    /** APPROVE — POST /admin/price-requests/{id}/approve
     *  Admin may have edited the pricing fields (all except customer & product)
     *  on the review screen — those edits are validated, saved onto the request,
     *  and the master check is re-run against the edited values.
     */
    public function approve(Request $request, $id)
    {
        $pr = CustomerPriceRequest::with('customer')->findOrFail($id);

        if ($pr->status !== 'Pending') {
            return redirect()->back()->with('flash_message_error', 'This request has already been ' . strtolower($pr->status) . '.');
        }

        $customer = $pr->customer;
        if (!$customer) {
            return redirect()->back()->with('flash_message_error', 'Customer no longer exists.');
        }
        if (!in_array($customer->business_model, ['Direct Customer', 'Hybrid'])) {
            return redirect()->back()->with('flash_message_error', 'Customer business model is ' . $customer->business_model . ' — price requests apply only to Direct Customer / Hybrid.');
        }

        // Validate the (possibly edited) fields — customer & product stay locked
        $rules = [
            'payment_term'           => 'bail|required|in:' . implode(',', payment_terms()),
            'freight_basis'          => 'bail|required|in:Paid by Company,Paid by Customer',
            'packing_size'           => 'bail|required|in:' . implode(',', array_keys(packing_sizes())),
            'final_customer_price'   => 'bail|required|numeric|gt:0',
            'selling_expense_basis'  => 'bail|required|in:%,Rs/kg',
            'selling_expense_value'  => 'bail|required|numeric|min:0',
        ];
        if ($request->input('freight_basis') == 'Paid by Company') {
            $rules['freight'] = 'bail|required|numeric|gt:0';
        }
        $validator = Validator::make($request->all(), $rules, [
            'freight.gt' => 'Freight must be greater than 0 when paid by company.',
        ]);
        if (!$validator->passes()) {
            return redirect()->back()->with('flash_message_error', $validator->messages()->first());
        }

        // Apply admin edits onto the request. BUT payment_term / freight_basis /
        // freight are only editable when EMPTY in the master — if the master already
        // has a value, that value is authoritative and any posted edit is ignored.
        $masterPtFilled = trim((string) $customer->payment_term) !== '';
        $masterFbFilled = trim((string) $customer->freight_basis) !== '';
        $masterFrFilled = !($customer->freight === null || $customer->freight === '');

        $pr->payment_term  = $masterPtFilled ? $customer->payment_term  : $request->payment_term;
        $pr->freight_basis = $masterFbFilled ? $customer->freight_basis : $request->freight_basis;
        if ($masterFrFilled) {
            $pr->freight = (float) $customer->freight;
        } else {
            $pr->freight = ($pr->freight_basis == 'Paid by Company') ? $request->freight : 0;
        }

        // Freely editable pricing fields
        $pr->packing_size          = $request->packing_size;
        $pr->final_customer_price  = $request->final_customer_price;
        $pr->selling_expense_basis = $request->selling_expense_basis;
        $pr->selling_expense_value = $request->selling_expense_value;

        $v = customer_viability_check(
            getProductStandardDp($pr->product_id),
            $pr->payment_term,
            $pr->packing_size,
            $pr->freight_basis,
            $pr->freight,
            $pr->selling_expense_basis,
            $pr->selling_expense_value,
            $pr->final_customer_price
        );
        $pr->final_msp               = $v['minimum_selling_price'];
        $pr->selling_expenses        = $v['selling_expenses'];
        $pr->additional_realization  = $v['additional_realization'];

        // ⚠ THE IMPORTANT CHECK — block on mismatch, fill empties (against edited values)
        $check = $this->compareWithMaster($pr, $customer);
        if (!empty($check['mismatches'])) {
            return redirect()->back()->with(
                'flash_message_error',
                'Cannot approve — customer master mismatch: ' . implode(' | ', $check['mismatches']) .
                '. Edit the fields to match, correct the customer master, or reject this request.'
            );
        }

        DB::beginTransaction();
        try {
            // Fill empty master fields from the (edited) request
            if (!empty($check['fill'])) {
                foreach ($check['fill'] as $col => $val) {
                    $customer->{$col} = $val;
                }
                if (array_key_exists('payment_term', $check['fill'])) {
                    $customer->payment_term_type = 'On Bill';
                }
                $customer->save();
            }

            // Push the approved price into customer_discounts (net_products).
            // If a row already exists for this customer+product, it is replaced.
            $custDis = CustomerDiscount::firstOrNew([
                'customer_id'   => $pr->customer_id,
                'product_id'    => $pr->product_id,
                'discount_type' => 'net_products',
            ]);
            $custDis->net_price             = $pr->final_customer_price;   // Customer Selling Price
            $custDis->packing_type          = $pr->packing_size;           // Packing Size
            $custDis->selling_expense_basis = $pr->selling_expense_basis ?: '%';
            $custDis->selling_expense_value = $pr->selling_expense_value;
            $custDis->moq                   = $custDis->moq ?: 0;
            $custDis->has_special           = $custDis->has_special ?: 'no';
            $custDis->save();

            $pr->status               = 'Approved';
            $pr->action_by            = auth()->user()->id;
            $pr->action_at            = now();
            $pr->customer_discount_id = $custDis->id;
            $pr->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_message_error', 'Approval failed: ' . $e->getMessage());
        }

        return redirect(url('/admin/price-requests?status=Pending'))
            ->with('flash_message_success', 'Request #PR-' . $pr->id . ' approved — price saved to customer products.');
    }

    /** REJECT — POST /admin/price-requests/{id}/reject  (reason required) */
    public function reject(Request $request, $id)
    {
        $pr = CustomerPriceRequest::findOrFail($id);

        if ($pr->status !== 'Pending') {
            return redirect()->back()->with('flash_message_error', 'This request has already been ' . strtolower($pr->status) . '.');
        }

        $validator = Validator::make($request->all(), [
            'reject_reason' => 'bail|required|min:3',
        ], [
            'reject_reason.required' => 'Please give a reason for rejection.',
        ]);
        if (!$validator->passes()) {
            return redirect()->back()->with('flash_message_error', $validator->messages()->first());
        }

        $pr->status        = 'Rejected';
        $pr->reject_reason = $request->reject_reason;
        $pr->action_by     = auth()->user()->id;
        $pr->action_at     = now();
        $pr->save();

        return redirect(url('/admin/price-requests?status=Pending'))
            ->with('flash_message_success', 'Request #PR-' . $pr->id . ' rejected.');
    }
}