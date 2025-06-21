<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('raw_materials')->delete();
        DB::table('packing_sizes')->delete();
        DB::table('products')->delete();
        DB::table('product_raw_materials')->delete();
        DB::table('dealers')->delete();
        DB::table('customers')->delete();
        DB::table('customer_cities')->delete();
        DB::table('customer_employees')->delete();
        $path = storage_path('app/custom_seeds/products.sql');
        $process = new Process([
            'mysql',
            '-h',
            DB::getConfig('host'),
            '-u',
            DB::getConfig('username'),
            '-p' . DB::getConfig('password'),
            DB::getConfig('database'),
            '-e',
            "source $path"
        ]);
        $process->mustRun();
    }
}
