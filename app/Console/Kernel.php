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
        \App\Console\Commands\SendMonthlyVisitAnalysis::class,
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
            ->between('8:30', '9:15')
            ->timezone('Asia/Kolkata')
            ->withoutOverlapping();

        // Runs only on the 1st of every month — reports on the PREVIOUS month
        // (e.g. runs 1 Sep, reports August). The ->when() gate is a safety net
        // in case the window ever spans a month boundary.
        /*$schedule->command('report:monthly-visit-analysis')
            ->everyMinute()
            ->between('6:00', '7:30')
            ->timezone('Asia/Kolkata')
            ->when(fn () => now('Asia/Kolkata')->day === 1)
            ->withoutOverlapping();*/


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
