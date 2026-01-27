<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Dealer;
use App\DealerOperatingCity;
use DB;

class MigrateDealerOperatingCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dealer:migrate-operating-cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate dealer_operating_cities and migrate city for parent dealers only';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting dealer operating cities migration...');

        DB::beginTransaction();
        try {

            // 🔥 Disable FK checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 🔥 Truncate table
            DB::table('dealer_operating_cities')->truncate();

            // 🔥 Enable FK checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // ✅ Only parent dealers
            $dealers = Dealer::whereNull('parent_id')
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->get();

            foreach ($dealers as $dealer) {

                $dealerCity = new DealerOperatingCity();
                $dealerCity->dealer_id = $dealer->id;
                $dealerCity->city = $dealer->city;
                $dealerCity->save();

                $this->line("✔ Dealer ID {$dealer->id} → {$dealer->city}");
            }

            DB::commit();
            $this->info('Migration completed successfully.');

        } catch (\Exception $e) {

            DB::rollback();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->error('Migration failed!');
            $this->error($e->getMessage());
        }

        return 0;
    }
}
