<?php

use Illuminate\Database\Seeder;

class DesignationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('designations')->delete();
        DB::select("INSERT INTO `designations` (`id`, `department_id`, `parent_id`, `designation`, `type`, `multiple_region`, `multiple_sub_region`, `status`, `created_at`, `updated_at`) VALUES
		(1, 2, 'ROOT', 'Country Head', 'region', 1, 1, 1, '2021-05-04 03:55:15', '2021-05-04 03:55:15'),
		(2, 2, '1', 'Regional Head', 'region', 0, 0, 1, '2021-05-04 03:55:28', '2021-05-04 03:55:28'),
		(3, 2, '2', 'Sub Regional Representative', 'region', 0, 0, 1, '2021-05-04 03:56:11', '2021-05-04 03:56:11'),
		(4, 2, '3', 'Techincal Executive', 'region', 0, 0, 1, '2021-05-04 03:56:35', '2021-05-04 03:56:35')");
    }
}
