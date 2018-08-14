<?php

namespace App\Services;

use App\Platforms\Binance;
use Illuminate\Support\Facades\Redis;

class StrategyService
{
     const UP = 1;
     const DOWN_ONE = -1;
     const DOWN_TWO = -2;
     const BINANCE_FEE = 0.002;

     //黑三兵后买 固定偏移卖 minute
     public static function BlackThree($platform = PlatformService::BINANCE, $symbol = 'BTC/USDT', $period = '1m', $profitPercent = 0.002)
     {
          if ($platform == 'binance') {
               //满足黑三兵
               $ticker = implode('', explode('/', $symbol));
               $api = new Binance(Config('run')['get_platform_key'], Config('run')['get_platform_secret']);
//               $ticks = $api->candlesticks($ticker, $period);
//               $endSecond = array_slice($ticks,-2,1)[0];

               $endSecond =  [
                   "open" => "6299.49000000",
                   "high" => "6309.09000000",
                   "low" => "6297.32000000",
                   "close" => "6306.01000000",
                   "volume" => "192033.73528155",
                   "openTime" => 1534086270000,
                   "closeTime" => 1534086299999,
                   "assetVolume" => "30.45977900",
                   "baseVolume" => "192033.73528155",
                   "trades" => 124,
                   "assetBuyVolume" => "20.72199400",
                   "takerBuyVolume" => "130628.70280336",
                   "ignored" => "0"
                 ];
               $closePrice = $endSecond['close'];

               //获取的数据是否更新
               $timeStampKey = $platform . $ticker . $period . 'timestamp';
               $timeStampSave = Redis::get($timeStampKey);
               if (is_null($timeStampSave)) {
                    Redis::set($timeStampKey, $endSecond['openTime']);
                    return null;
               }
               if ($timeStampSave == $endSecond['openTime']) return null;
               Redis::set($timeStampKey, $endSecond['openTime']);

               //价格变化黑三兵
               $change = $endSecond['close'] - $endSecond['open'];
               $changKey = $platform . $ticker . $period;
               $mark = Redis::get($changKey);
               if (is_null($mark)) {
                    if ($change >= 0) {
                         Redis::set($changKey, self::UP); //涨 1
                    } else {
                         Redis::set($changKey, self::DOWN_ONE); //跌 -1
                    }
                    return null;
               } else {
                    if ($change > 0) {  //涨
                         if ($mark < 0) {
                              Redis::set($changKey, self::UP);
                         }
                         return null;
                    } elseif ($change < 0) {  //跌
                         if ($mark == self::UP) {
                              Redis::set($changKey, self::DOWN_ONE);
                              //一次跌并高于标记价卖出
                                   //有未成交则不做
                         } elseif ($mark == self::DOWN_ONE) {
                              Redis::set($changKey, self::DOWN_TWO);
                         } elseif ($mark == self::DOWN_TWO) {
                              //有单不交易
                              $haveOrderKey = $platform . $ticker . $period . 'haveorder';
                              $orderId = Redis::get($haveOrderKey);
                              if (!is_null($orderId)) {
                                   //获取数据的账号有订单未完成则返回
                                   $status = OrderService::getOrderStatus($platform, $ticker, $orderId);
                                   $returnSatus = [OrderService::ORDER_STATUS_NEW, OrderService::ORDER_STATUS_PARTIALLY_FILLED];
                                   if (in_array($status, $returnSatus)) {
                                        return 'have not finished order';
                                   }
                              }

                              //跌三次后买
                              $buyPrice = OrderService::placeNormalOrder(OrderService::OPE_BUY, $platform, $symbol, '');
                              //标记卖单价
                              $sellPriceLineKey = $platform . $ticker . $period . 'sellline';
                              $sellPriceLinePrice = $buyPrice * (1 + self::BINANCE_FEE + $profitPercent);
                              Redis::set($sellPriceLineKey, $sellPriceLinePrice);
                              //标记下了买单
                              Redis::set();
                         }
                         return null;
                    } else {
                         return null;
                    }
               }

          }

          //return no ope
          return null;
     }
}
