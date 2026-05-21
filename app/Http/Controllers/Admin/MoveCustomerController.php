<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use DB;
use Session;
use App\UserCustomerShare;

class MoveCustomerController extends Controller
{
    /**
     * Show the Move Customers form.
     * GET /admin/move-customers
     */
    public function index()
    {
        $title = 'Move Customers';

        // Marketing department ID = 2
        $marketingDeptId = 2;

        // Source dropdown: only Marketing employees
        $employees = DB::table('users')
            ->join('user_departments', 'user_departments.user_id', '=', 'users.id')
            ->where('users.type', 'employee')
            ->where('user_departments.department_id', $marketingDeptId)
            ->orderBy('users.name')
            ->select('users.id', 'users.name', 'users.designation')
            ->distinct()
            ->get();

        // "Move To" dropdown: only Marketing employees
        $moveToUsers = DB::table('users')
            ->join('user_departments', 'user_departments.user_id', '=', 'users.id')
            ->where('users.type', 'employee')
            ->where('user_departments.department_id', $marketingDeptId)
            ->orderBy('users.name')
            ->select('users.id', 'users.name', 'users.designation')
            ->distinct()
            ->get();

        return view('admin.move_customers.index', compact('title', 'employees', 'moveToUsers'));
    }

    /**
     * Load customers for the selected employee + all subordinates (recursive).
     * GET /admin/move-customers/load-customers?user_id=X
     * Returns JSON.
     */
    public function loadCustomers(Request $request)
    {
        $userId = $request->get('user_id');

        if (!$userId) {
            return response()->json(['error' => 'No user selected'], 400);
        }

        // Get the selected employee
        $rootUser = DB::table('users')->where('id', $userId)->first();
        if (!$rootUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Build the full hierarchy: selected user + all subordinates (recursive)
        $allUserIds = $this->getAllSubordinateIds($userId);
        array_unshift($allUserIds, (int)$userId); // put root first
        $allUserIds = array_unique($allUserIds);

        // Fetch user info for all IDs
        $usersMap = DB::table('users')
            ->whereIn('id', $allUserIds)
            ->select('id', 'name', 'designation')
            ->get()
            ->keyBy('id');

        // Fetch customers for each user via user_customer_shares
        // LEFT JOIN customer_cities in the same query — no N+1
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

        // Build the response array in hierarchy order
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
                'customers'   => array_map(function($c) {
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
     * Move selected customers to a new user.
     * POST /admin/move-customers/move
     */
    public function moveCustomers(Request $request)
    {
        $customerIds        = $request->get('customer_ids', []);
        $fromUserId         = $request->get('from_user_id');
        $toUserId           = $request->get('to_user_id');
        $bmFilter           = $request->get('bm_filter', '');
        $cityFilter         = $request->get('city_filter', '');
        $sourceEmployeeId   = $request->get('source_employee_id', '');

        // Basic validation
        if (empty($customerIds) || !$toUserId) {
            Session::flash('error', 'Please select at least one customer and a target user.');
            return Redirect::back();
        }

        $moved   = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($customerIds as $item) {
                // Each item is "originalUserId_customerId"
                $parts          = explode('_', $item);
                $originalUserId = $parts[0];
                $customerId     = $parts[1];

                // Skip if customer already belongs to target user
                if ($originalUserId == $toUserId) {
                    $skipped++;
                    continue;
                }

                // Check if target user already has this customer assigned
                $existing = DB::table('user_customer_shares')
                    ->where('user_id', $toUserId)
                    ->where('customer_id', $customerId)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Get original share record
                $originalShare = DB::table('user_customer_shares')
                    ->where('user_id', $originalUserId)
                    ->where('customer_id', $customerId)
                    ->first();

                if (!$originalShare) {
                    $skipped++;
                    continue;
                }

                // Insert for new user
                DB::table('user_customer_shares')->insert([
                    'user_id'       => $toUserId,
                    'customer_id'   => $customerId,
                    'share'         => $originalShare->share,
                    'user_date'     => $originalShare->user_date,
                    'average_sales' => $originalShare->average_sales,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Remove from original user
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

        // Build query params to restore the UI state after redirect
        $queryParams = [];
        if ($sourceEmployeeId) {
            $queryParams['source_employee'] = $sourceEmployeeId;
        }
        if ($bmFilter) {
            $queryParams['bm_filter'] = $bmFilter;
        }
        if ($cityFilter) {
            $queryParams['city_filter'] = $cityFilter;
        }

        return Redirect::route('admin.move-customers.index', $queryParams);
    }

    /**
     * Recursively get all subordinate user IDs for a given user,
     * based on user_departments.report_to -> users.id chain.
     *
     * @param  int   $userId
     * @param  array $visited  (prevent infinite loops)
     * @return array
     */
    private function getAllSubordinateIds($userId, $visited = [])
    {
        // Prevent circular references
        if (in_array($userId, $visited)) {
            return [];
        }
        $visited[] = $userId;

        // Find users that report to $userId via user_departments
        $subordinateIds = DB::table('user_departments')
            ->where('report_to', $userId)
            ->pluck('user_id')
            ->toArray();

        $subordinateIds = array_unique($subordinateIds);

        $allIds = [];
        foreach ($subordinateIds as $subId) {
            $allIds[] = (int)$subId;
            // Recurse
            $deeper = $this->getAllSubordinateIds($subId, $visited);
            $allIds = array_merge($allIds, $deeper);
        }

        return array_unique($allIds);
    }
}