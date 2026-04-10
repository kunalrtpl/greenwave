<?php
// FILE 2: database/seeds/AddMiscellaneousLeaveTypeSeeder.php
// Run: php artisan db:seed --class=AddMiscellaneousLeaveTypeSeeder

use Illuminate\Database\Seeder;
use App\LeaveType;

class AddMiscellaneousLeaveTypeSeeder extends Seeder
{
    public function run()
    {
        // Miscellaneous Leave — no quota, admin grants it as a one-off holiday
        // quota_editable = false (no quota to edit)
        // has_quota      = false (no balance tracking)
        LeaveType::updateOrCreate(
            ['code' => 'ML'],
            [
                'name'           => 'Miscellaneous Leave',
                'code'           => 'ML',
                'has_quota'      => false,   // No balance deduction
                'quota_editable' => false,
                'default_quota'  => 0,
                'color'          => '#8e44ad',
                'is_active'      => true,
                'sort_order'     => 5,
            ]
        );

        $this->command->info('Miscellaneous Leave (ML) seeded.');
    }
}