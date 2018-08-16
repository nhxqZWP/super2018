<?php

namespace App\Console;

use App\Services\OrderService;
use App\Services\StrategyService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         ini_set('memory_limit', '1000M'); //内存限制
         $schedule->call(function () {
              for ($i = 0; $i < 6; $i++) {
                   $result = StrategyService::BlackThree();
                   Log::debug($result);
                   sleep(5);
              }
         })->cron('* * * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
//        $this->load(__DIR__.'/Commands');
//
//        require base_path('routes/console.php');
    }
}
