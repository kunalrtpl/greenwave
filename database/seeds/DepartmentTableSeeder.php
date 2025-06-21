<?php

use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('departments')->delete();
        DB::select("INSERT INTO `departments` (`id`, `department`, `status`, `created_at`, `updated_at`) VALUES
		(1, 'Production', 1, '2021-05-04 01:18:56', '2021-05-04 01:18:56'),
		(2, 'Marketing', 1, '2021-05-04 01:19:01', '2021-05-04 01:19:01'),
		(3, 'R & D', 1, '2021-05-04 01:19:06', '2021-05-04 01:19:06'),
		(4, 'Accounts', 1, '2021-05-04 01:20:24', '2021-05-04 01:20:24'),
		(5, 'HR', 1, '2021-05-04 01:20:30', '2021-05-04 01:20:30')");
    }
}
