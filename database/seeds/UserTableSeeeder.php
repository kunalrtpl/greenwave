<?php

use Illuminate\Database\Seeder;

class UserTableSeeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->delete();
        DB::select('INSERT INTO `users` (`id`, `type`, `name`, `dob`, `mobile`, `email`, `email_verified_at`, `password`, `correspondence_address`, `permanent_address`, `image`, `pan`, `aadhar`, `joining_date`, `joining_type`, `permanent_from`, `salary_account_no`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, "admin", "Super Admin", "", "9876543210", "super@greenwave.com", NULL, "$2y$10$cuZQOYw3lGsQif6RfYbjV.l0eCM9S4SgQKctzB8KbJX5qD/q2NOly", "Ludhiana", "Ludhiana","4738.jpg", "", "", "", "", "", "", 1, NULL, NULL, NULL)');
    }
}
