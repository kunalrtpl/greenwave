<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use DB;
use Session;
use PDF; // barryvdh/laravel-dompdf

class DealerMoveCustomerController extends Controller
{
    /**
     * Show the Dealer Move Customers form.
     * GET /admin/dealer-move-customers
     */
    public function index()
    {
        Session::put('active', 'dealerMoveCustomers');
        $title = 'Move Customers by Dealer / Business Model';

        $dealers = DB::table('dealers')
            ->select('id', 'business_name')
            ->whereNull('parent_id')
            ->orderBy('business_name')
            ->get();

        $moveToOptions = $dealers;

        return view(
            'admin.dealer_move_customers.index',
            compact('title', 'dealers', 'moveToOptions')
        );
    }

    /**
     * Load customers for a selected source.
     * GET /admin/dealer-move-customers/load-customers
     * Returns JSON.
     */
    public function loadCustomers(Request $request)
    {
        $sourceType = $request->get('source_type');
        $dealerId   = $request->get('dealer_id');

        if (!$sourceType) {
            return response()->json(['error' => 'No source selected'], 400);
        }

        $query = DB::table('customers')
            ->leftJoin('dealers', 'dealers.id', '=', 'customers.dealer_id')
            ->leftJoin('customer_cities', 'customer_cities.customer_id', '=', 'customers.id')
            // Join to get linked user via user_customer_shares
            ->leftJoin('user_customer_shares', 'user_customer_shares.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'users.id', '=', 'user_customer_shares.user_id');

        if ($sourceType === 'dealer') {
            if (!$dealerId) {
                return response()->json(['error' => 'No dealer selected'], 400);
            }
            $query->where('customers.business_model', 'Dealer')
                  ->where('customers.dealer_id', $dealerId);
        } else {
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
                'customer_cities.city_name',
                // User info
                'users.id as user_id',
                'users.name as user_name',
                'users.designation as user_designation'
            )
            ->orderBy('customers.name')
            ->get();

        return response()->json([
            'success'     => true,
            'source_type' => $sourceType,
            'dealer_id'   => $dealerId,
            'data'        => $customers,
        ]);
    }

    /**
     * Move selected customers to a new business model / dealer.
     * POST /admin/dealer-move-customers/move
     */
    public function moveCustomers(Request $request)
    {
        $customerIds    = $request->get('customer_ids', []);
        $toType         = $request->get('to_type');
        $toDealerId     = $request->get('to_dealer_id');
        $cityFilter     = $request->get('city_filter', '');
        $userFilter     = $request->get('user_filter', '');
        $sourceType     = $request->get('source_type', '');
        $sourceDealerId = $request->get('source_dealer_id', '');

        if (empty($customerIds) || !$toType) {
            Session::flash('error', 'Please select at least one customer and a target.');
            return Redirect::back();
        }

        if ($toType === 'dealer' && !$toDealerId) {
            Session::flash('error', 'Please select a target dealer.');
            return Redirect::back();
        }

        if ($toType === 'Direct Customer') {
            $newBusinessModel = 'Direct Customer';
            $newDealerId      = null;
        } elseif ($toType === 'Open') {
            $newBusinessModel = 'Open';
            $newDealerId      = null;
        } else {
            $newBusinessModel = 'Dealer';
            $newDealerId      = $toDealerId;
        }

        $moved   = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($customerIds as $customerId) {
                $customerId = (int) $customerId;

                $customer = DB::table('customers')
                    ->where('id', $customerId)
                    ->select('id', 'business_model', 'dealer_id')
                    ->first();

                if (!$customer) {
                    $skipped++;
                    continue;
                }

                $alreadySame = (
                    $customer->business_model === $newBusinessModel
                    && (string)$customer->dealer_id === (string)$newDealerId
                );

                if ($alreadySame) {
                    $skipped++;
                    continue;
                }

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

        $queryParams = [];
        if ($sourceType)     $queryParams['source_type']     = $sourceType;
        if ($sourceDealerId) $queryParams['source_dealer_id'] = $sourceDealerId;
        if ($cityFilter)     $queryParams['city_filter']      = $cityFilter;
        if ($userFilter)     $queryParams['user_filter']      = $userFilter;

        return Redirect::route('admin.dealer-move-customers.index', $queryParams);
    }

    /**
     * Export PDF of currently filtered customers.
     * GET /admin/dealer-move-customers/export-pdf
     */
    public function exportPdf(Request $request)
    {
        $sourceType = $request->get('source_type');
        $dealerId   = $request->get('dealer_id');
        $cityFilter = $request->get('city_filter', '');
        $userFilter = $request->get('user_filter', '');

        // Build the query same as loadCustomers
        $query = DB::table('customers')
            ->leftJoin('dealers', 'dealers.id', '=', 'customers.dealer_id')
            ->leftJoin('customer_cities', 'customer_cities.customer_id', '=', 'customers.id')
            ->leftJoin('user_customer_shares', 'user_customer_shares.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'users.id', '=', 'user_customer_shares.user_id');

        if ($sourceType === 'dealer') {
            $query->where('customers.business_model', 'Dealer')
                  ->where('customers.dealer_id', $dealerId);
        } elseif ($sourceType) {
            $query->where('customers.business_model', $sourceType);
        }

        // Apply city filter
        if ($cityFilter) {
            $query->where('customer_cities.city_name', $cityFilter);
        }

        // Apply user filter
        if ($userFilter) {
            $query->where('users.id', $userFilter);
        }

        $customers = $query
            ->select(
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.contact_person_name',
                'customers.designation as customer_designation',
                'customers.department',
                'customers.business_model',
                'customers.dealer_id',
                'dealers.business_name as dealer_business_name',
                'customer_cities.city_name',
                'users.id as user_id',
                'users.name as user_name',
                'users.designation as user_designation'
            )
            ->orderBy('customers.name')
            ->get();

        // Build label for header
        $sourceLabel = $sourceType ?? 'All';
        if ($sourceType === 'dealer' && $dealerId) {
            $dealer = DB::table('dealers')->where('id', $dealerId)->first();
            $sourceLabel = $dealer ? $dealer->business_name : 'Dealer';
        }

        $filterLabels = [];
        if ($cityFilter)     $filterLabels[] = 'City: ' . $cityFilter;
        if ($userFilter) {
            $user = DB::table('users')->where('id', $userFilter)->first();
            if ($user) $filterLabels[] = 'User: ' . $user->name;
        }

        $pdf = PDF::loadView('admin.dealer_move_customers.pdf', [
            'customers'    => $customers,
            'sourceLabel'  => $sourceLabel,
            'filterLabels' => $filterLabels,
            'generatedAt'  => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('customers-' . str_slug($sourceLabel) . '-' . now()->format('Ymd') . '.pdf');
    }
}