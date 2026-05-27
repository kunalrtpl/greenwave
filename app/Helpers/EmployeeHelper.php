<?php
// app/Helpers/EmployeeHelper.php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeHelper
{
    /**
     * Get all Marketing department employees who have at least one customer assigned,
     * regardless of whether the employee is active or inactive.
     *
     * Rules:
     *  - Active   + customers > 0  → INCLUDED
     *  - Inactive + customers > 0  → INCLUDED
     *  - Active   + customers = 0  → EXCLUDED
     *  - Inactive + customers = 0  → EXCLUDED
     *
     * @param  int   $departmentId   Default: 2 (Marketing)
     * @return Collection            Each item has: id, name, designation, status, customer_count
     */
    public static function getEmployeesWithCustomers(int $departmentId = 2): Collection
    {
        return DB::table('users')
            ->join('user_departments', 'user_departments.user_id', '=', 'users.id')
            ->join(
                DB::raw('(SELECT user_id, COUNT(*) as customer_count FROM user_customer_shares GROUP BY user_id) as ucs'),
                'ucs.user_id', '=', 'users.id'
            )
            ->where('users.type', 'employee')
            ->where('user_departments.department_id', $departmentId)
            ->where('ucs.customer_count', '>', 0)          // Core rule: must have customers
            ->orderBy('users.name')
            ->select(
                'users.id',
                'users.name',
                'users.designation',
                'users.status',                            // active / inactive — for UI badge display
                DB::raw('ucs.customer_count')
            )
            ->distinct()
            ->get();
    }
}