<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\EarnedLeaveAccrualCommand::class,
        \App\Console\Commands\ScheduleHeartbeat::class,
        \App\Console\Commands\SendDailyWorkReport::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('attendance:el-accrual')
         ->monthlyOn(1, '00:05')  // Runs at 00:05 on the 1st of every month
         ->withoutOverlapping()
         ->runInBackground()
         ->appendOutputTo(storage_path('logs/el-accrual.log'));


        $schedule->command('report:daily-work-email')
            ->everyMinute()
            ->between('9:00', '14:00')
            ->timezone('Asia/Kolkata')
            ->withoutOverlapping();

        // TESTING SETUP: Appending the specific target arguments for 2026-07-12
        $schedule->command('report:daily-work-email --date=2026-07-12 --limit=2')
        ->everyMinute()
        ->between('9:00', '14:00')
        ->timezone('Asia/Kolkata')
        ->withoutOverlapping();

        //$schedule->command('heartbeat:check')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
