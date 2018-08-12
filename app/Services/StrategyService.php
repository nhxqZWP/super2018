<?php

namespace App\Service;

use App\Platforms\Binance;

class StrategyService
{
     //黑三兵后买 固定偏移卖 minute
     public static function BlackThree($platform = 'binance', $symbol = 'BTC/USDT', $period = '1m')
     {
          if ($platform == 'binance') {
               //满足黑三兵
               $ticker = implode('', explode('/', $symbol));
               $api = new Binance(Config('run')['get_platform_key'], Config('run')['get_platform_secret']);
               $ticks = $api->candlesticks($ticker, $period);
               dd($ticks);
               $endSecond = array_slice($ticks,-2,1);
               $closePrice = $endSecond[0]['close'];

               $change = $endSecond[0]['close'] - $endSecond[0]['open'];
               if ($change > 0) {
                    Redis::set('binance:price_change', 1);  //1-涨 2-跌
                    return 1;
               } elseif ($change < 0) {
                    Redis::set('binance:price_change', 2);  //1-涨 2-跌
                    return 2;
               } else {
                    return null;
               }
          }

          //return no ope
          return null;
     }
}