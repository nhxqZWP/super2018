<?php

namespace App\Services;

use App\Platforms\Binance;
use Illuminate\Support\Facades\Redis;

class StrategyService
{
     const UP = 1; //涨 不分次数
     const DOWN_ONE = -1; //跌一次
     const DOWN_TWO = -2; //跌两次
     const DOWN_MORE = -3; //跌三次及以上
     const BINANCE_FEE = 0.002;
     const PROFIT_FEE_PERCENT = 0.002;
     const THREE_DOWN_BTCUSDT = 'three_down_btcusdt_minute';

     //黑三兵后买 固定偏移卖 minute
     public static function BlackThree($platform = PlatformService::BINANCE, $symbol = 'BTC/USDT', $period = '1m', $profitPercent = self::PROFIT_FEE_PERCENT)
     {
          if ($platform == PlatformService::BINANCE) {
               //满足黑三兵
               $ticker = implode('', explode('/', $symbol));
               $api = new Binance(Config('run')['get_platform_key'], Config('run')['get_platform_secret']);
               $ticks = $api->candlesticks($ticker, $period);
               $endSecond = array_slice($ticks,-2,1)[0];
//               $endSecond =  [
//                   "open" => "6299.49000000",
//                   "high" => "6309.09000000",
//                   "low" => "6297.32000000",
//                   "close" => "6306.01000000",
//                   "volume" => "192033.73528155",
//                   "openTime" => 1534086270000,
//                   "closeTime" => 1534086299999,
//                   "assetVolume" => "30.45977900",
//                   "baseVolume" => "192033.73528155",
//                   "trades" => 124,
//                   "assetBuyVolume" => "20.72199400",
//                   "takerBuyVolume" => "130628.70280336",
//                   "ignored" => "0"
//                 ];
               $openTime = date('H:i:s', intval($endSecond['openTime']/1000));

               //用时间戳标记数据是否更新
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
               $sellPriceLineKey = $platform . $ticker . $period . 'sellline';
               $haveOrderKey = $platform . $ticker . $period . 'haveorder';
               if (is_null($mark)) { //第一次进来
                    if ($change >= 0) {
                         Redis::set($changKey, self::UP); //涨 1
                         return 'mark price up '.$openTime;
                    } else {
                         Redis::set($changKey, self::DOWN_ONE); //跌 -1
                         return 'mark price down '.$openTime;
                    }
               } else {
                    if ($change > 0) {  //涨
                         if ($mark < 0) {
                              Redis::set($changKey, self::UP);
                         }
                         return 'mark price up '.$openTime;
                    } elseif ($change < 0) {  //跌
                         if ($mark == self::UP) {
                              Redis::set($changKey, self::DOWN_ONE);
                              //一次跌并高于标记价卖出
                              $getSellPrice = Redis::get($sellPriceLineKey);
                              if (is_null($getSellPrice)) return 'mark price down once '.$openTime;
                              $currentPrice = OrderService::getOnePrice(PlatformService::BINANCE, $ticker);
                              if ($currentPrice < $getSellPrice) return 'current price less than sell price line';
                              //有未成交则不做
                              $orderId = Redis::get($haveOrderKey);
                              if (!is_null($orderId)) {
                                   $status = OrderService::getOrderStatus($platform, $ticker, $orderId); //只看了数据来源的账号有没有单未完成
                                   $returnSatus = [OrderService::ORDER_STATUS_NEW, OrderService::ORDER_STATUS_PARTIALLY_FILLED];
                                   if (in_array($status, $returnSatus)) {
                                        return 'have not finished order';
                                   }
                              }
                              //卖出并消除卖单价标记
                              $quantity = 0;
                              $doAccount = Config('run')['do_trade'];
                              foreach ($doAccount as $plat => $account) {
                                   list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByGivenPrice($plat, $account['symbol'], $getSellPrice, $account['key'], $account['secret']);
                                   if (!is_null($orderRes)) {
                                        return $orderRes;
                                   }
                              }
                              //记录卖单id
                              Redis::set($haveOrderKey, $orderId);
                              //删除标记的卖单价格
                              Redis::del($sellPriceLineKey);
                              return 'place sell order quantity '. $quantity . ' price ' . $getSellPrice;
                         } elseif ($mark == self::DOWN_ONE) {
                              Redis::set($changKey, self::DOWN_TWO);
                              return 'mark price down twice '.$openTime;
                         } elseif ($mark == self::DOWN_TWO) {
                              //有单不交易
                              $orderId = Redis::get($haveOrderKey);
                              if (!is_null($orderId)) {
                                   //获取数据的账号有订单未完成则返回
                                   $status = OrderService::getOrderStatus($platform, $ticker, $orderId); //只看了数据来源的账号有没有单未完成
                                   $returnSatus = [OrderService::ORDER_STATUS_NEW, OrderService::ORDER_STATUS_PARTIALLY_FILLED];
                                   if (in_array($status, $returnSatus)) {
                                        return 'have not finished order';
                                   }
                              }

                              //跌三次后买
                              $quantity = 0;
                              $doAccount = Config('run')['do_trade'];
                              foreach ($doAccount as $plat => $account) {
                                   list($orderRes, $buyPrice, $orderId, $quantity) = OrderService::placeBuyOrderByCurrentPrice($plat, $account['symbol'], $account['key'], $account['secret']);
                                   if (!is_null($orderRes)) {
                                        return $orderRes;
                                   }
                              }
                              //记录买单id
                              Redis::set($haveOrderKey, $orderId);
                              //标记卖单价
                              $sellPriceLinePrice = $buyPrice * (1 + self::BINANCE_FEE + $profitPercent);
                              Redis::set($sellPriceLineKey, $sellPriceLinePrice);
                              return 'place buy order quantity '. $quantity . ' price ' . $buyPrice;
                         } else {
                              Redis::set($changKey, self::DOWN_MORE);
                              return 'mark price down more '.$openTime; //跌第四次及以上了
                         }
                    } else {
                         return 'price not change';
                    }
               }

          }

          //return no ope
          return 'not is binance';
     }
}
