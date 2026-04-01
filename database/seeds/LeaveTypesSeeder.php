<?php

use Illuminate\Database\Seeder;
use App\LeaveType;

/**
 * LeaveTypesSeeder
 *
 * Run: php artisan db:seed --class=LeaveTypesSeeder
 *
 * ─── quota_editable FLAG LOGIC ───────────────────────────────────────────────
 *  true  → Admin CAN edit the total_quota in user_leave_quotas (Sick, Casual)
 *  false → Admin CANNOT edit total_quota (Earned Leave — accrual-based, protected)
 *
 * ─── has_quota FLAG LOGIC ────────────────────────────────────────────────────
 *  true  → Leave deducts from user_leave_quotas
 *  false → No quota tracking (LWP — user just takes unpaid leave, no balance needed)
 */
class LeaveTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name'            => 'Sick Leave',
                'code'            => 'SL',
                'has_quota'       => true,
                'quota_editable'  => true,   // ✅ Admin can update sick leave balance
                'default_quota'   => 12.0,   // 12 days per year
                'color'           => '#FF6B6B',
                'is_active'       => true,
                'sort_order'      => 1,
            ],
            [
                'name'            => 'Casual Leave',
                'code'            => 'CL',
                'has_quota'       => true,
                'quota_editable'  => true,   // ✅ Admin can update casual leave balance
                'default_quota'   => 12.0,
                'color'           => '#4ECDC4',
                'is_active'       => true,
                'sort_order'      => 2,
            ],
            [
                'name'            => 'Earned Leave',
                'code'            => 'EL',
                'has_quota'       => true,
                'quota_editable'  => false,  // 🔒 Admin CANNOT directly edit (accrual-based)
                'default_quota'   => 15.0,
                'color'           => '#45B7D1',
                'is_active'       => true,
                'sort_order'      => 3,
            ],
            [
                'name'            => 'Leave Without Pay',
                'code'            => 'LWP',
                'has_quota'       => false,  // 🚫 No quota — unlimited but unpaid
                'quota_editable'  => false,
                'default_quota'   => 0,
                'color'           => '#95A5A6',
                'is_active'       => true,
                'sort_order'      => 4,
            ],
            // Future: easy to add more types here
            // [
            //     'name'           => 'Maternity Leave',
            //     'code'           => 'ML',
            //     'has_quota'      => true,
            //     'quota_editable' => false,
            //     'default_quota'  => 180.0,
            //     ...
            // ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }

        $this->command->info('✅ Leave types seeded successfully.');
    }
}