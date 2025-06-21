<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ModuleTableSeeder::class);
        $this->call(UserTableSeeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(StateTableSeeder::class);
        $this->call(CityTableSeeder::class);
        $this->call(DepartmentTableSeeder::class);
        $this->call(DesignationTableSeeder::class);
        $this->call(RegionTableSeeder::class);
        $this->call(ProductTableSeeder::class);
    }
}
