<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use DB;
use Session;
use PDF;
use App\Helpers\EmployeeHelper;

class MoveCustomerController extends Controller
{
    /**
     * Show the Move Customers form.
     * GET /admin/move-customers
     */
    public function index()
    {
        Session::put('active', 'moveCustomers');
        $title = 'Move Customers';

        $employees   = EmployeeHelper::getEmployeesWithCustomers();
        $moveToUsers = EmployeeHelper::getEmployeesWithCustomers();

        return view('admin.move_customers.index', compact('title', 'employees', 'moveToUsers'));
    }

    /**
     * Load customers for the selected employee + all subordinates (recursive).
     * Subordinates with 0 customers AND inactive status are excluded.
     * GET /admin/move-customers/load-customers?user_id=X
     */
    public function loadCustomers(Request $request)
    {
        $userId = $request->get('user_id');

        if (!$userId) {
            return response()->json(['error' => 'No user selected'], 400);
        }

        $rootUser = DB::table('users')->where('id', $userId)->first();
        if (!$rootUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Build full hierarchy: selected user + all subordinates (recursive)
        $allUserIds = $this->getAllSubordinateIds($userId);
        array_unshift($allUserIds, (int)$userId);
        $allUserIds = array_unique($allUserIds);

        // ── Filter out inactive subordinates who have 0 customers ──
        // Root user always stays regardless of status or customer count
        $allUserIds = $this->filterValidSubordinates($allUserIds, (int)$userId);

        // Fetch user info for all IDs
        $usersMap = DB::table('users')
            ->whereIn('id', $allUserIds)
            ->select('id', 'name', 'designation', 'status')
            ->get()
            ->keyBy('id');

        // Fetch customers via user_customer_shares — no N+1
        $shares = DB::table('user_customer_shares')
            ->whereIn('user_customer_shares.user_id', $allUserIds)
            ->join('customers', 'customers.id', '=', 'user_customer_shares.customer_id')
            ->leftJoin('dealers', 'dealers.id', '=', 'customers.dealer_id')
            ->leftJoin('customer_cities', 'customer_cities.customer_id', '=', 'customers.id')
            ->select(
                'user_customer_shares.user_id',
                'user_customer_shares.customer_id',
                'user_customer_shares.share',
                'user_customer_shares.user_date',
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
            ->orderBy('user_customer_shares.user_id')
            ->orderBy('customers.name')
            ->get();

        // Group by user_id
        $grouped = [];
        foreach ($shares as $share) {
            $grouped[$share->user_id][] = $share;
        }

        // Build response in hierarchy order
        $result = [];
        foreach ($allUserIds as $uid) {
            $user = isset($usersMap[$uid]) ? $usersMap[$uid] : null;
            if (!$user) continue;

            $customers = isset($grouped[$uid]) ? $grouped[$uid] : [];

            $result[] = [
                'user_id'     => $uid,
                'user_name'   => $user->name,
                'designation' => $user->designation,
                'is_root'     => ($uid == $userId),
                'customers'   => array_map(function ($c) {
                    return [
                        'customer_id'          => $c->customer_id,
                        'customer_name'        => $c->customer_name,
                        'contact_person_name'  => $c->contact_person_name,
                        'designation'          => $c->customer_designation,
                        'department'           => $c->department,
                        'category'             => $c->category,
                        'share'                => $c->share,
                        'user_date'            => $c->user_date,
                        'business_model'       => $c->business_model,
                        'dealer_id'            => $c->dealer_id,
                        'dealer_business_name' => $c->dealer_business_name,
                        'city_name'            => $c->city_name,
                    ];
                }, $customers),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Export customer list to PDF respecting filters.
     * GET /admin/move-customers/export-pdf
     */
    public function exportPdf(Request $request)
    {
        $userId    = $request->get('user_id');
        $bmFilter  = $request->get('bm_filter', '');
        $cityFilter = $request->get('city_filter', '');

        if (!$userId) {
            Session::flash('error', 'No employee selected for PDF export.');
            return Redirect::route('admin.move-customers.index');
        }

        $rootUser = DB::table('users')->where('id', $userId)->first();
        if (!$rootUser) {
            Session::flash('error', 'Employee not found.');
            return Redirect::route('admin.move-customers.index');
        }

        // Build hierarchy
        $allUserIds = $this->getAllSubordinateIds($userId);
        array_unshift($allUserIds, (int)$userId);
        $allUserIds = array_unique($allUserIds);
        $allUserIds = $this->filterValidSubordinates($allUserIds, (int)$userId);

        // Fetch user info
        $usersMap = DB::table('users')
            ->whereIn('id', $allUserIds)
            ->select('id', 'name', 'designation')
            ->get()
            ->keyBy('id');

        // Fetch customers
        $query = DB::table('user_customer_shares')
            ->whereIn('user_customer_shares.user_id', $allUserIds)
            ->join('customers', 'customers.id', '=', 'user_customer_shares.customer_id')
            ->leftJoin('dealers', 'dealers.id', '=', 'customers.dealer_id')
            ->leftJoin('customer_cities', 'customer_cities.customer_id', '=', 'customers.id')
            ->select(
                'user_customer_shares.user_id',
                'user_customer_shares.customer_id',
                'customers.name as customer_name',
                'customers.contact_person_name',
                'customers.designation as customer_designation',
                'customers.department',
                'customers.business_model',
                'dealers.business_name as dealer_business_name',
                'customer_cities.city_name'
            )
            ->orderBy('user_customer_shares.user_id')
            ->orderBy('customers.name');

        // Apply BM filter
        if ($bmFilter) {
            if (in_array($bmFilter, ['Direct Customer', 'Open'])) {
                $query->where('customers.business_model', $bmFilter);
            } else {
                // Dealer name filter
                $query->where('customers.business_model', 'Dealer')
                      ->where('dealers.business_name', $bmFilter);
            }
        }

        // Apply city filter
        if ($cityFilter) {
            $query->where('customer_cities.city_name', $cityFilter);
        }

        $shares = $query->get();

        // Group by user_id
        $grouped = [];
        foreach ($shares as $share) {
            $grouped[$share->user_id][] = $share;
        }

        // Build groups array
        $groups = [];
        foreach ($allUserIds as $uid) {
            $user = isset($usersMap[$uid]) ? $usersMap[$uid] : null;
            if (!$user) continue;
            $customers = isset($grouped[$uid]) ? $grouped[$uid] : [];
            if (empty($customers)) continue; // skip empty groups in PDF

            $groups[] = [
                'user_name'   => $user->name,
                'designation' => $user->designation,
                'is_root'     => ($uid == $userId),
                'customers'   => $customers,
            ];
        }

        $filterLabel = '';
        if ($bmFilter)   $filterLabel .= 'Business Model: ' . $bmFilter . '  ';
        if ($cityFilter) $filterLabel .= 'City: ' . $cityFilter;

        $pdf = PDF::loadView('admin.move_customers.pdf', [
            'groups'       => $groups,
            'rootUser'     => $rootUser,
            'filterLabel'  => trim($filterLabel),
            'generatedAt'  => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'customers_' . str_replace(' ', '_', $rootUser->name) . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Move selected customers to a new user.
     * POST /admin/move-customers/move
     */
    public function moveCustomers(Request $request)
    {
        $customerIds      = $request->get('customer_ids', []);
        $fromUserId       = $request->get('from_user_id');
        $toUserId         = $request->get('to_user_id');
        $bmFilter         = $request->get('bm_filter', '');
        $cityFilter       = $request->get('city_filter', '');
        $sourceEmployeeId = $request->get('source_employee_id', '');

        if (empty($customerIds) || !$toUserId) {
            Session::flash('error', 'Please select at least one customer and a target user.');
            return Redirect::back();
        }

        $moved   = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($customerIds as $item) {
                $parts          = explode('_', $item);
                $originalUserId = $parts[0];
                $customerId     = $parts[1];

                if ($originalUserId == $toUserId) { $skipped++; continue; }

                $existing = DB::table('user_customer_shares')
                    ->where('user_id', $toUserId)
                    ->where('customer_id', $customerId)
                    ->first();
                if ($existing) { $skipped++; continue; }

                $originalShare = DB::table('user_customer_shares')
                    ->where('user_id', $originalUserId)
                    ->where('customer_id', $customerId)
                    ->first();
                if (!$originalShare) { $skipped++; continue; }

                DB::table('user_customer_shares')->insert([
                    'user_id'       => $toUserId,
                    'customer_id'   => $customerId,
                    'share'         => $originalShare->share,
                    'user_date'     => $originalShare->user_date,
                    'average_sales' => $originalShare->average_sales,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                DB::table('user_customer_shares')
                    ->where('user_id', $originalUserId)
                    ->where('customer_id', $customerId)
                    ->delete();

                $moved++;
            }

            DB::commit();

            $msg = "{$moved} customer(s) moved successfully.";
            if ($skipped > 0) {
                $msg .= " {$skipped} skipped (already assigned to target user).";
            }
            Session::flash('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'An error occurred: ' . $e->getMessage());
        }

        $queryParams = [];
        if ($sourceEmployeeId) $queryParams['source_employee'] = $sourceEmployeeId;
        if ($bmFilter)         $queryParams['bm_filter']       = $bmFilter;
        if ($cityFilter)       $queryParams['city_filter']     = $cityFilter;

        return Redirect::route('admin.move-customers.index', $queryParams);
    }

    /**
     * Filter user IDs: keep root always, keep subordinates only if
     * they have customers > 0 OR are active (inactive + 0 customers → excluded).
     */
    private function filterValidSubordinates(array $allUserIds, int $rootUserId): array
    {
        // Get customer counts per user
        $counts = DB::table('user_customer_shares')
            ->whereIn('user_id', $allUserIds)
            ->select('user_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id')
            ->toArray();

        // Get status per user
        $statuses = DB::table('users')
            ->whereIn('id', $allUserIds)
            ->pluck('status', 'id')
            ->toArray();

        $filtered = [];
        foreach ($allUserIds as $uid) {
            // Root user always included
            if ($uid === $rootUserId) {
                $filtered[] = $uid;
                continue;
            }

            $customerCount = isset($counts[$uid]) ? (int)$counts[$uid] : 0;
            $isActive      = isset($statuses[$uid]) && $statuses[$uid] == 1;

            // Exclude: inactive AND zero customers
            if (!$isActive && $customerCount === 0) {
                continue;
            }

            $filtered[] = $uid;
        }

        return $filtered;
    }

    /**
     * Recursively get all subordinate user IDs.
     */
    private function getAllSubordinateIds($userId, $visited = [])
    {
        if (in_array($userId, $visited)) {
            return [];
        }
        $visited[] = $userId;

        $subordinateIds = DB::table('user_departments')
            ->where('report_to', $userId)
            ->pluck('user_id')
            ->toArray();

        $subordinateIds = array_unique($subordinateIds);

        $allIds = [];
        foreach ($subordinateIds as $subId) {
            $allIds[] = (int)$subId;
            $deeper   = $this->getAllSubordinateIds($subId, $visited);
            $allIds   = array_merge($allIds, $deeper);
        }

        return array_unique($allIds);
    }
}