<?php

namespace App\Services;

use App\Platforms\Binance;

class TargetService
{
     public static function getMACD($pair = 'BTCUSDT', $period = '1h', $short=12,$long=26,$m=9)
     {
          //Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
          $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
          $kList = $api->candlesticks($pair, $period);

          $kStockList = [];
          $i = 0;
          foreach ($kList as $k => $v) {
               if ($i == 0) {
                    $kStockList[$i]['ema12'] = $v['close'];
                    $kStockList[$i]['ema26'] = $v['close'];
                    $kStockList[$i]['dif'] = 0;
                    $kStockList[$i]['dea'] = 0;
                    $kStockList[$i]['macd'] = 0;
                    $kStockList[$i]['timestamp'] = $k;
                    $i++;
               } else {
                    $kStockList[$i]['ema12'] = (2.0 * $v['close'] + ($short - 1) * $kStockList[$i - 1]['ema12']) / ($short + 1);
                    $kStockList[$i]['ema26'] = (2.0 * $v['close'] + ($long - 1) * $kStockList[$i - 1]['ema26']) / ($long + 1);
                    $kStockList[$i]['dif'] = $kStockList[$i]['ema12'] - $kStockList[$i]['ema26'];
                    $kStockList[$i]['dea'] = (2.0 * $kStockList[$i]['dif'] + ($m - 1) * $kStockList[$i - 1]['dea']) / ($m + 1);
//                $kStockList[$k]['macd'] = 2.0 * ($kStockList[$k]['dif'] - $kStockList[$k]['dea']);
                    $kStockList[$i]['macd'] = $kStockList[$i]['dif'] - $kStockList[$i]['dea'];
                    $kStockList[$i]['timestamp'] = $k;
                    $i++;
               }
          }
          return array_reverse($kStockList);
     }
}