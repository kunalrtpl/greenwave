<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ScheduleHeartbeat extends Command
{
    // The name and signature of the console command
    protected $signature = 'heartbeat:check';

    // The console command description
    protected $description = 'Logs a heartbeat to verify the scheduler is running';

    public function handle()
    {
        $now = now()->toDateTimeString();

        // 1. Log to a specific file (storage/logs/heartbeat.log)
        Log::info("Scheduler Heartbeat: Still running at {$now}");

        // 2. Store in Cache (to check via a UI or API later)
        //Cache::put('scheduler_last_ran_at', $now);

        $this->info("Heartbeat logged at {$now}");
    }
}