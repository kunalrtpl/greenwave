<?php
// app/Helpers/EmployeeHelper.php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeHelper
{
    /**
     * Get Marketing department employees for the main table.
     *
     * Rules:
     *  - Active   + customers = 0  → INCLUDED  (show with zero counts)
     *  - Active   + customers > 0  → INCLUDED
     *  - Inactive + customers > 0  → INCLUDED
     *  - Inactive + customers = 0  → EXCLUDED
     *
     * @param  int   $departmentId   Default: 2 (Marketing)
     * @return Collection  id, name, designation, status,
     *                     customer_count, direct_count, open_count, dealer_count
     */
    public static function getEmployeesWithCustomerStats(int $departmentId = 2): Collection
    {
        return DB::table('users')
            ->join('user_departments', 'user_departments.user_id', '=', 'users.id')
            // LEFT JOIN so employees with 0 customers are still returned
            ->leftJoin(
                DB::raw('(SELECT user_id, COUNT(*) as customer_count FROM user_customer_shares GROUP BY user_id) as ucs'),
                'ucs.user_id', '=', 'users.id'
            )
            ->leftJoin(
                DB::raw('(
                    SELECT ucs2.user_id, COUNT(*) as direct_count
                    FROM user_customer_shares ucs2
                    JOIN customers c2 ON c2.id = ucs2.customer_id
                    WHERE c2.business_model = \'Direct Customer\'
                    GROUP BY ucs2.user_id
                ) as dc'),
                'dc.user_id', '=', 'users.id'
            )
            ->leftJoin(
                DB::raw('(
                    SELECT ucs3.user_id, COUNT(*) as open_count
                    FROM user_customer_shares ucs3
                    JOIN customers c3 ON c3.id = ucs3.customer_id
                    WHERE c3.business_model = \'Open\'
                    GROUP BY ucs3.user_id
                ) as oc'),
                'oc.user_id', '=', 'users.id'
            )
            ->leftJoin(
                DB::raw('(
                    SELECT ucs4.user_id, COUNT(*) as dealer_count
                    FROM user_customer_shares ucs4
                    JOIN customers c4 ON c4.id = ucs4.customer_id
                    WHERE c4.business_model = \'Dealer\'
                    GROUP BY ucs4.user_id
                ) as dlc'),
                'dlc.user_id', '=', 'users.id'
            )
            ->where('users.type', 'employee')
            ->where('user_departments.department_id', $departmentId)
            ->where(function ($q) {
                // Active employees always shown (even with 0 customers)
                // Inactive only shown if they have at least 1 customer
                $q->where('users.status', 1)
                  ->orWhere(function ($q2) {
                      $q2->where('users.status', '!=', 1)
                         ->whereRaw('COALESCE(ucs.customer_count, 0) > 0');
                  });
            })
            ->orderBy('users.name')
            ->select(
                'users.id',
                'users.name',
                'users.designation',
                'users.status',
                DB::raw('COALESCE(ucs.customer_count, 0) as customer_count'),
                DB::raw('COALESCE(dc.direct_count, 0) as direct_count'),
                DB::raw('COALESCE(oc.open_count, 0) as open_count'),
                DB::raw('COALESCE(dlc.dealer_count, 0) as dealer_count')
            )
            ->distinct()
            ->get();
    }

    /**
     * Get employees for the "Move To" dropdown.
     *
     * Rules:
     *  - Active   + any customer count → INCLUDED (can receive customers)
     *  - Inactive + customers > 0      → INCLUDED
     *  - Inactive + customers = 0      → EXCLUDED
     *
     * @param  int   $departmentId
     * @return Collection  id, name, designation, status, customer_count
     */
    public static function getEmployeesWithCustomers(int $departmentId = 2): Collection
    {
        return DB::table('users')
            ->join('user_departments', 'user_departments.user_id', '=', 'users.id')
            ->leftJoin(
                DB::raw('(SELECT user_id, COUNT(*) as customer_count FROM user_customer_shares GROUP BY user_id) as ucs'),
                'ucs.user_id', '=', 'users.id'
            )
            ->where('users.type', 'employee')
            ->where('user_departments.department_id', $departmentId)
            ->where(function ($q) {
                // Active employees always appear — they can receive customers even if they have none yet
                // Inactive only appear if they already have customers
                $q->where('users.status', 1)
                  ->orWhere(function ($q2) {
                      $q2->where('users.status', '!=', 1)
                         ->whereRaw('COALESCE(ucs.customer_count, 0) > 0');
                  });
            })
            ->orderBy('users.name')
            ->select(
                'users.id',
                'users.name',
                'users.designation',
                'users.status',
                DB::raw('COALESCE(ucs.customer_count, 0) as customer_count')
            )
            ->distinct()
            ->get();
    }
}