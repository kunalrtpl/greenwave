<?php

use Illuminate\Database\Seeder;

class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('countries')->delete();
        DB::select("INSERT INTO `countries` (`id`, `country_name`, `status`, `sort`, `created_at`, `updated_at`) VALUES
		(1, 'India', 1, 1, '2021-01-01 00:31:44', '2021-01-01 00:31:44')");
    }
}
