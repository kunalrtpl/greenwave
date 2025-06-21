<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
class RegionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('regions')->delete();
        $path = storage_path('app/custom_seeds/regions.sql');
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
