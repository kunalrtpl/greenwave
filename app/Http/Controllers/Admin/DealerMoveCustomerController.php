<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use DB;
use Session;

class DealerMoveCustomerController extends Controller
{
    /**
     * Show the Dealer Move Customers form.
     * GET /admin/dealer-move-customers
     */
    public function index()
    {
        Session::put('active','dealerMoveCustomers'); 
        $title = 'Move Customers by Dealer / Business Model';

        // Source options: Direct Customer, Open, + all dealers
        $dealers = DB::table('dealers')
            ->select('id', 'business_name')
            ->whereNULL('parent_id')
            ->orderBy('business_name')
            ->get();

        // "Move To" options: same list
        $moveToOptions = $dealers;

        return view(
            'admin.dealer_move_customers.index',
            compact('title', 'dealers', 'moveToOptions')
        );
    }

    /**
     * Load customers for a selected source (business_model or dealer).
     * GET /admin/dealer-move-customers/load-customers
     *   ?source_type=Direct Customer   → business_model = 'Direct Customer'
     *   ?source_type=Open              → business_model = 'Open'
     *   ?source_type=dealer&dealer_id=5 → business_model = 'Dealer' AND dealer_id = 5
     *
     * Returns JSON.
     */
    public function loadCustomers(Request $request)
    {
        $sourceType = $request->get('source_type');   // 'Direct Customer' | 'Open' | 'dealer'
        $dealerId   = $request->get('dealer_id');      // only used when source_type = 'dealer'

        if (!$sourceType) {
            return response()->json(['error' => 'No source selected'], 400);
        }

        // Build base query
        $query = DB::table('customers')
            ->leftJoin('dealers', 'dealers.id', '=', 'customers.dealer_id')
            ->leftJoin('customer_cities', 'customer_cities.customer_id', '=', 'customers.id');

        if ($sourceType === 'dealer') {
            if (!$dealerId) {
                return response()->json(['error' => 'No dealer selected'], 400);
            }
            $query->where('customers.business_model', 'Dealer')
                  ->where('customers.dealer_id', $dealerId);
        } else {
            // 'Direct Customer' or 'Open'
            $query->where('customers.business_model', $sourceType);
        }

        $customers = $query
            ->select(
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact_person_name',
                'customers.designation as customer_designation',
                'customers.department',
                'customers.category',
                'customers.business_model',
                'customers.dealer_id',
                'dealers.business_name as dealer_business_name',
                'customer_cities.city_name'
            )
            ->orderBy('customers.name')
            ->get();

        return response()->json([
            'success'   => true,
            'source_type' => $sourceType,
            'dealer_id'   => $dealerId,
            'data'      => $customers,
        ]);
    }

    /**
     * Move selected customers to a new business model / dealer.
     * POST /admin/dealer-move-customers/move
     *
     * Posted fields:
     *   customer_ids[]   — array of customer IDs
     *   to_type          — 'Direct Customer' | 'Open' | 'dealer'
     *   to_dealer_id     — dealer ID (only when to_type = 'dealer')
     *   source_type      — for redirect restore
     *   source_dealer_id — for redirect restore
     *   city_filter      — for redirect restore
     */
    public function moveCustomers(Request $request)
    {
        $customerIds   = $request->get('customer_ids', []);
        $toType        = $request->get('to_type');
        $toDealerId    = $request->get('to_dealer_id');
        $cityFilter    = $request->get('city_filter', '');
        $sourceType    = $request->get('source_type', '');
        $sourceDealerId = $request->get('source_dealer_id', '');

        // Basic validation
        if (empty($customerIds) || !$toType) {
            Session::flash('error', 'Please select at least one customer and a target.');
            return Redirect::back();
        }

        if ($toType === 'dealer' && !$toDealerId) {
            Session::flash('error', 'Please select a target dealer.');
            return Redirect::back();
        }

        // Determine new business_model value and dealer_id for the update
        if ($toType === 'Direct Customer') {
            $newBusinessModel = 'Direct Customer';
            $newDealerId      = null;
        } elseif ($toType === 'Open') {
            $newBusinessModel = 'Open';
            $newDealerId      = null;
        } else {
            // dealer
            $newBusinessModel = 'Dealer';
            $newDealerId      = $toDealerId;
        }

        $moved   = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($customerIds as $customerId) {
                $customerId = (int) $customerId;

                // Fetch current record
                $customer = DB::table('customers')
                    ->where('id', $customerId)
                    ->select('id', 'business_model', 'dealer_id')
                    ->first();

                if (!$customer) {
                    $skipped++;
                    continue;
                }

                // Skip if already in the same destination
                $alreadySame = (
                    $customer->business_model === $newBusinessModel
                    && (string)$customer->dealer_id === (string)$newDealerId
                );

                if ($alreadySame) {
                    $skipped++;
                    continue;
                }

                // Update the customer record
                DB::table('customers')
                    ->where('id', $customerId)
                    ->update([
                        'business_model' => $newBusinessModel,
                        'dealer_id'      => $newDealerId,
                        'updated_at'     => now(),
                    ]);

                $moved++;
            }

            DB::commit();

            $msg = "{$moved} customer(s) moved successfully.";
            if ($skipped > 0) {
                $msg .= " {$skipped} skipped (already in target or not found).";
            }
            Session::flash('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'An error occurred: ' . $e->getMessage());
        }

        // Build query params to restore UI state after redirect
        $queryParams = [];
        if ($sourceType)     $queryParams['source_type']     = $sourceType;
        if ($sourceDealerId) $queryParams['source_dealer_id'] = $sourceDealerId;
        if ($cityFilter)     $queryParams['city_filter']      = $cityFilter;

        return Redirect::route('admin.dealer-move-customers.index', $queryParams);
    }
}