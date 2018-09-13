<?php

namespace App\Services;

use App\Platforms\Binance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class StrategyService
{
     const UP = 1; //涨 不分次数
     const DOWN_ONE = -1; //跌一次
     const DOWN_TWO = -2; //跌两次
     const DOWN_THREEE = -3; //跌三次
     const DOWN_MORE = -4; //跌四次及以上
     const BINANCE_FEE = 0.002;
     const PROFIT_FEE_PERCENT = 0.001;
     const STOP_LOSS_PRICE_DEL = 400; //差400usdt止损
     const THREE_DOWN_BTCUSDT = 'three_down_btcusdt_minute';

     //黑三兵后买 固定偏移卖 minute
     public static function BlackThree($platform = PlatformService::BINANCE, $symbol = 'BTC/USDT', $period = '3m', $profitPercent = self::PROFIT_FEE_PERCENT)
     {
          date_default_timezone_set('PRC');
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
               $closePrice = $endSecond['close'];

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
                         return 'mark price up '.$openTime.' '.$closePrice;
                    } else {
                         Redis::set($changKey, self::DOWN_ONE); //跌 -1
                         return 'mark price down '.$openTime.' '.$closePrice;
                    }
               } else {
                    if ($change > 0) {  //涨
                         if ($mark < 0) {
                              Redis::set($changKey, self::UP);
                              //判断是否到了止损价
                              $getSellPrice = Redis::get($sellPriceLineKey);
                              if (!is_null($getSellPrice)) { //有买单成交并标记了卖单
                                   $changeL = $getSellPrice - $closePrice;
                                   if ($changeL > self::STOP_LOSS_PRICE_DEL) {
                                        //止损 卖出并消除卖单价标记
                                        $quantity = 0;
                                        $doAccount = Config('run')['do_trade'];
                                        foreach ($doAccount as $plat => $account) {
                                             if (!empty($account['key'])) {
                                                  list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                                                  if ($plat === 'binance') {
                                                       if (!is_null($orderRes)) {
                                                            return $orderRes;
                                                       }
                                                       $quantityUsed = $quantity;
                                                       $orderIdUsed = $orderId;
                                                  } else {
                                                       Log::debug('key2 place sell stop less  order quantity '. $quantity . ' price ' . $closePrice);
                                                  }
                                             }
                                        }
                                        //记录卖单id
                                        Redis::set($haveOrderKey, $orderIdUsed);
                                        //删除标记的卖单价格
                                        Redis::del($sellPriceLineKey);
                                        return 'place sell stop less order quantity '. $quantityUsed . ' price ' . $closePrice;
                                   }
                              }
                         }
                         $getSellPrice = Redis::get($sellPriceLineKey);
                         return 'mark price up '.$openTime.' now:'.$closePrice.' sellLine:'.$getSellPrice;
                    } elseif ($change < 0) {  //跌
                         if ($mark == self::UP) {
                              Redis::set($changKey, self::DOWN_ONE);
                              //一次跌并高于标记价卖出
                              $getSellPrice = Redis::get($sellPriceLineKey);
                              if (is_null($getSellPrice)) return 'mark price down once '.$openTime.' '.$closePrice;
                              $currentPrice = OrderService::getOnePrice(PlatformService::BINANCE, $ticker);
                              if ($currentPrice < $getSellPrice) return 'current price less than sell price line '.$getSellPrice;
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
                                   if (!empty($account['key'])) {
                                        list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByGivenPrice(trim($plat), $account['symbol'], $getSellPrice, $account['key'], $account['secret']);
                                        if ($plat === 'binance') {
                                             if (!is_null($orderRes)) {
                                                  return $orderRes;
                                             }
                                             $quantityUsed = $quantity;
                                             $orderIdUsed = $orderId;
                                        } else {
                                             Log::debug('key2 place sell order quantity '. $quantity . ' price ' . $getSellPrice);
                                        }
                                   }
                              }
                              //记录卖单id
                              Redis::set($haveOrderKey, $orderIdUsed);
                              //删除标记的卖单价格
                              Redis::del($sellPriceLineKey);
                              return 'place sell order quantity '. $quantityUsed . ' price ' . $getSellPrice;
                         } elseif ($mark == self::DOWN_ONE) {
                              Redis::set($changKey, self::DOWN_TWO);
                              return 'mark price down twice '.$openTime.' '.$closePrice;
                         } elseif ($mark == self::DOWN_TWO) {
                              Redis::set($changKey, self::DOWN_THREEE);
                              return 'mark price down third '.$openTime.' '.$closePrice;
                         } elseif ($mark == self::DOWN_THREEE) {
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

                              //跌四次后买
                              $quantity = 0;
                              $doAccount = Config('run')['do_trade'];
                              foreach ($doAccount as $plat => $account) {
                                   if (!empty($account['key'])) {
                                        list($orderRes, $buyPrice, $orderId, $quantity) = OrderService::placeBuyOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                                        if ($plat === 'binance') {
                                             if (!is_null($orderRes)) {
                                                  return $orderRes;
                                             }
                                             $buyPriceUsed = $buyPrice;
                                             $quantityUsed = $quantity;
                                             $orderIdUsed = $orderId;
                                        } else {
                                             Log::debug('key2 place buy order quantity '. $quantity . ' price ' . $buyPrice);
                                        }
                                   }
                              }
                              //记录买单id
                              Redis::set($haveOrderKey, $orderIdUsed);
                              //标记卖单价
                              $sellPriceLinePrice = $buyPriceUsed * (1 + self::BINANCE_FEE + $profitPercent);
                              Redis::set($sellPriceLineKey, $sellPriceLinePrice);
                              Redis::set($changKey, self::DOWN_MORE);
                              return 'place buy order quantity '. $quantityUsed . ' price ' . $buyPriceUsed;
                         } else {
                              return 'mark price down more '.$openTime.' '.$closePrice; //跌第四次及以上了
                         }
                    } else {
                         return 'price not change';
                    }
               }

          }

          //return no ope
          return 'not is binance';
     }


     //黑三兵后买 固定偏移卖 5min 低于最近12小时最低点卖 EOS/USDT：价格小数点后4位 数量小数点后2位
     public static function BlackThree2($platform = PlatformService::BINANCE, $symbol = 'EOS/USDT', $period = '5m', $profitPercent = self::PROFIT_FEE_PERCENT)
     {
          date_default_timezone_set('PRC');
          if ($platform == PlatformService::BINANCE) {
               //满足黑三兵
               $ticker = implode('', explode('/', $symbol));
               $api = new Binance(Config('run')['get_platform_key'], Config('run')['get_platform_secret']);
               $ticks = $api->candlesticks($ticker, $period);
               $endSecond = array_slice($ticks,-2,1)[0];
               $openTime = date('H:i:s', intval($endSecond['openTime']/1000));
               $closePrice = $endSecond['close'];

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
                         return 'mark price up '.$openTime.' '.$closePrice;
                    } else {
                         Redis::set($changKey, self::DOWN_ONE); //跌 -1
                         return 'mark price down '.$openTime.' '.$closePrice;
                    }
               } else {
                    if ($change > 0) {  //涨
                         if ($mark < 0) {
                              Redis::set($changKey, self::UP);
                              //判断是否到了止损价（12小时内最低点）到了则卖出 并标记此个12小时已使用
                              $change = PlatformService::getIsLowerThanLowestPrice($closePrice, $ticker, '3d');
                              if ($change) {
                                   $quantity = 0;
                                   $doAccount = Config('run')['do_trade2'];
                                   foreach ($doAccount as $plat => $account) {
                                        if (!empty($account['key'])) {
                                             list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                                             if ($plat === 'binance') {
                                                  if (!is_null($orderRes)) {
                                                       return $orderRes;
                                                  }
                                                  $quantityUsed = $quantity;
                                                  $orderIdUsed = $orderId;
                                             } else {
                                                  Log::debug('key2 place sell stop less  order quantity '. $quantity . ' price ' . $closePrice);
                                             }
                                        }
                                   }
                                   //记录卖单id
                                   Redis::set($haveOrderKey, $orderIdUsed);
                                   //删除标记的卖单价格
                                   Redis::del($sellPriceLineKey);
                                   //删除标记的3d止损价
                                   PlatformService::delLowestPriceSince();
                                   return 'place sell stop less order quantity '. $quantityUsed . ' price ' . $closePrice;
                              }
                         }
                         $getSellPrice = Redis::get($sellPriceLineKey);
                         return 'mark price up '.$openTime.' now:'.$closePrice.' sellLine:'.$getSellPrice;
                    } elseif ($change < 0) {  //跌
                         if ($mark == self::UP) {
                              Redis::set($changKey, self::DOWN_ONE);
                              //一次跌并高于标记价卖出
                              $getSellPrice = Redis::get($sellPriceLineKey);
                              if (is_null($getSellPrice)) return 'mark price down once '.$openTime.' '.$closePrice;
                              $currentPrice = OrderService::getOnePrice(PlatformService::BINANCE, $ticker);
                              if ($currentPrice < $getSellPrice) return 'current price less than sell price line '.$getSellPrice;
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
                              $doAccount = Config('run')['do_trade2'];
                              foreach ($doAccount as $plat => $account) {
                                   if (!empty($account['key'])) {
                                        list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByGivenPrice(trim($plat), $account['symbol'], $getSellPrice, $account['key'], $account['secret']);
                                        if ($plat === 'binance') {
                                             if (!is_null($orderRes)) {
                                                  return $orderRes;
                                             }
                                             $quantityUsed = $quantity;
                                             $orderIdUsed = $orderId;
                                        } else {
                                             Log::debug('key2 place sell order quantity '. $quantity . ' price ' . $getSellPrice);
                                        }
                                   }
                              }
                              //记录卖单id
                              Redis::set($haveOrderKey, $orderIdUsed);
                              //删除标记的卖单价格
                              Redis::del($sellPriceLineKey);
                              return 'place sell order quantity '. $quantityUsed . ' price ' . $getSellPrice;
                         } elseif ($mark == self::DOWN_ONE) {
                              Redis::set($changKey, self::DOWN_TWO);
                              return 'mark price down twice '.$openTime.' '.$closePrice;
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
                              $doAccount = Config('run')['do_trade2'];
                              foreach ($doAccount as $plat => $account) {
                                   if (!empty($account['key'])) {
                                        list($orderRes, $buyPrice, $orderId, $quantity) = OrderService::placeBuyOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                                        if ($plat === 'binance') {
                                             if (!is_null($orderRes)) {
                                                  return $orderRes;
                                             }
                                             $buyPriceUsed = $buyPrice;
                                             $quantityUsed = $quantity;
                                             $orderIdUsed = $orderId;
                                        } else {
                                             Log::debug('key2 place buy order quantity '. $quantity . ' price ' . $buyPrice);
                                        }
                                   }
                              }
                              //记录买单id
                              Redis::set($haveOrderKey, $orderIdUsed);
                              //标记卖单价
                              $sellPriceLinePrice = $buyPriceUsed * (1 + self::BINANCE_FEE + $profitPercent);
                              Redis::set($sellPriceLineKey, $sellPriceLinePrice);
                              Redis::set($changKey, self::DOWN_MORE);
                              return 'place buy order quantity '. $quantityUsed . ' price ' . $buyPriceUsed;
                         } else {
                              return 'mark price down more '.$openTime.' '.$closePrice; //跌第四次及以上了
                         }
                    } else {
                         return 'price not change';
                    }
               }

          }

          //return no ope
          return 'not is binance';
     }

     //macd底与负多少后上涨买入，高于正多少后下降卖出
     public static function changeMACD($platform = PlatformService::BINANCE, $symbol = 'EOS/USDT', $period = '15m')
     {
          date_default_timezone_set('PRC');
          $ticker = implode('', explode('/', $symbol));
          $haveOrderBuyKey = $platform . $ticker . $period . 'have_buy_order';
          $haveOrderSellKey = $platform . $ticker . $period . 'have_sell_order';

          //判断是否到了止损价（12小时内最低点）到了则卖出 并标记此个12小时已使用
          $api = new Binance(Config('run')['get_platform_key'], Config('run')['get_platform_secret']);
          $ticks = $api->candlesticks($ticker, '1m');
          $endSecond = array_slice($ticks,-2,1)[0];
          $closePrice = $endSecond['close'];
          $change = PlatformService::getIsLowerThanLowestPrice($closePrice, $ticker);
          if ($change) { //如果到了止损价
               //没有买 无需卖
               $buyOrderHave = Redis::get($haveOrderBuyKey);
               if (is_null($buyOrderHave)) return 'have not buy, so not stop less';
               //有买 下止损卖
               $doAccount = Config('run')['do_trade'];
               foreach ($doAccount as $plat => $account) {
                    if (!empty($account['key'])) {
                         list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                         if ($plat === 'binance') {
                              if (!is_null($orderRes)) {
                                   return $orderRes;
                              }
                              $quantityUsed = $quantity;
                              $orderIdUsed = $orderId;
                         } else {
                              Log::debug('key2 place sell stop less  order quantity ' . $quantity . ' price ' . $closePrice);
                         }
                    }
               }

               //记录卖单id
               Redis::set($haveOrderSellKey, $orderIdUsed);
               //删掉买单id 可以下买单了
               Redis::del($haveOrderBuyKey);
               //删除标记的3d止损价
               PlatformService::delLowestPriceSince();
               return 'place sell stop less order quantity ' . $quantityUsed . ' price ' . $closePrice;
          }

          $key = $platform.$ticker.$period.'macd';
          $macds = TargetService::getMACD($ticker, $period);
          $markTime = Redis::get($key);
          $timeStamp = $macds[1]['timestamp'];
          //macd没变化
          if (!is_null($markTime) && $markTime == $timeStamp) return 'macd not change';
          Redis::set($key, $timeStamp);

          //macd有变化
          $nowMACD = $macds[1]['macd'];
          $preMACD = $macds[2]['macd'];
          $lowLine = -0.003; //eos 15min
          $highLine = 0.005; //eos 15min

          if($preMACD < $lowLine && $nowMACD > $preMACD) {
               //下买单
               $buyOrderHave = Redis::get($haveOrderBuyKey);
               if (!is_null($buyOrderHave)) return 'have made buy order now macd '.$nowMACD;

               $doAccount = Config('run')['do_trade'];
               foreach ($doAccount as $plat => $account) {
                    if (!empty($account['key'])) {
                         list($orderRes, $buyPrice, $orderId, $quantity) = OrderService::placeBuyOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                         if ($plat === 'binance') {
                              if (!is_null($orderRes)) {
                                   return $orderRes;
                              }
                              $buyPriceUsed = $buyPrice;
                              $quantityUsed = $quantity;
                              $orderIdUsed = $orderId;
                         } else {
                              Log::debug('key2 place buy order quantity '. $quantity . ' price ' . $buyPrice);
                         }
                    }
               }
               //记录买单id
               Redis::set($haveOrderBuyKey, $orderIdUsed);
               //删除卖单id 可以下卖单了
               Redis::del($haveOrderSellKey);
               return 'place buy order quantity '. $quantityUsed . ' price ' . $buyPriceUsed. ' macd '.$nowMACD;
          } elseif($preMACD > $highLine && $nowMACD < $preMACD) {
               //下卖单
               $sellOrderHave = Redis::get($haveOrderSellKey);
               if (!is_null($sellOrderHave)) return 'have made sell order '.$nowMACD;

               $doAccount = Config('run')['do_trade'];
               foreach ($doAccount as $plat => $account) {
                    if (!empty($account['key'])) {
                         list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
                         if ($plat === 'binance') {
                              if (!is_null($orderRes)) {
                                   return $orderRes;
                              }
                              $quantityUsed = $quantity;
                              $orderIdUsed = $orderId;
                         } else {
                              Log::debug('key2 place sell order quantity '. $quantity . ' price ' . $closePrice);
                         }
                    }
               }
               //记录卖单id
               Redis::set($haveOrderSellKey, $orderIdUsed);
               //删掉买单id 可以下买单了
               Redis::del($haveOrderBuyKey);
               return 'place sell order quantity '. $quantityUsed . ' price ' . $closePrice. ' macd '.$nowMACD;
          } else {
               return 'macd_'.$nowMACD;
          }
     }
}
