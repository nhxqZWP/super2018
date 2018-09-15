<?php

namespace App\Console;

use App\Services\OrderService;
use App\Services\PlatformService;
use App\Services\StrategyService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
//         ini_set('memory_limit', '1000M'); // 内存限制
         $key = StrategyService::THREE_DOWN_BTCUSDT;
         $status = Redis::get($key);
         if (is_null($status) || $status == 0) {
              Log::debug('not open');
              return null;
         }

//         $schedule->call(function () {
//              for ($i = 0; $i < 2; $i++) {
////                   $result = StrategyService::BlackThree();
//                   $result = StrategyService::BlackThree2();
//                   Log::debug($result);
//                   sleep(3);
//              }
//         })->cron('* * * * *');

         $schedule->call(function () {
               for ($i = 0; $i < 10; $i++) {
                    $log = StrategyService::changeMacdOffset();
                    if (!is_null($log)) {
                         Log::debug($log);
                    }
                    sleep(5);
               }
         })->cron('* * * * *');

         $schedule->call(function () {
             PlatformService::setLowestPriceSince('EOSUSDT', '3d');
         })->everyTenMinutes();
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
