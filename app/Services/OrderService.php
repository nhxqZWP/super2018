<?php

namespace App\Services;

use App\Platforms\Binance;

class OrderService
{
     const OPE_BUY = 0;  //买单操作
     const OPE_SELL = 1; //卖单操作

     const ORDER_STATUS_NEW = 10;
     const ORDER_STATUS_PARTIALLY_FILLED = 11;
     const ORDER_STATUS_FILLED = 12;
     const ORDER_STATUS_CANCELED = 13;
     const ORDER_STATUS_REJECTED = 14;
     const ORDER_STATUS_EXPIRED = 15;

     const ORDER_SIDE_BUY = 20;
     const ORDER_SIDE_SELL = 21;

     //以买单最高下买单 以卖单最低下卖单
     public static function placeNormalOrder($ope = null, $platform = null, $symbol = null, $mark = '', $amountPercent = 1)
     {
          if (is_null($ope) || is_null($platform) || is_null($symbol)) {
               return null;
          }

          if ($ope === self::OPE_BUY) {

//             return $buyPrice;
          } elseif ($ope == self::OPE_SELL) {

          }

          return null;
     }

     //以当前价格下买单
     public static function placeBuyOrderByCurrentPrice($platform = PlatformService::BINANCE, $symbol = 'BTC/USDT', $key, $secret)
     {
          $coins = explode('/', $symbol);
          $ticker = $coins[0] . $coins[1];
          $price = 0;
          $orderId = '';
          $quantity = 0;
          if ($platform == PlatformService::BINANCE) {
               $api = new Binance($key, $secret);
               $balance = $api->balances();
               $coin2 = $balance[$coins[1]]['available']; //usdt
               $price = $api->prices()[$ticker]; //币安获取的价格可以直接使用
               $quantity = self::coinShow($coin2 / $price);
               $res = $api->buy($ticker, $quantity, $price);
               if (isset($res['msg'])) {
                    return [$res['msg'].' buy '.$ticker.' q: '.$quantity.' p: '.$price, 0, '', 0];
               }
               $orderId = $res['orderId'];
          }
          return [null, $price, $orderId, $quantity];
     }

     //指定价格下卖单
     public static function placeSellOrderByGivenPrice($platform = PlatformService::BINANCE, $symbol = 'BTC/USDT', $price, $key, $secret)
     {
          $coins = explode('/', $symbol);
          $ticker = $coins[0] . $coins[1];
          $quantity = 0;
          $orderId = '';
          if ($platform == PlatformService::BINANCE) {
               $api = new Binance($key, $secret);
               $balance = $api->balances();
               $coin1 = $balance[$coins[0]]['available']; //btc
               $quantity = self::coinShow($coin1);
               $price = self::coinShowUsdt($price);
               $res = $api->sell($ticker, $quantity, $price);
               if (isset($res['msg'])) {
                    return [$res['msg'].' sell '.$ticker.' q: '.$quantity.' p: '.$price, 0, ''];
               }
               $orderId = $res['orderId'];
          }
          return [null, $quantity, $orderId];
     }

     //下市场单
     public static function placeMarketOrder($ope = null, $platform = null, $symbol = null)
     {
          if (is_null($ope) || is_null($platform) || is_null($symbol)) {
               return null;
          }
     }

     //获取订单状态
     public static function getOrderStatus($platform = PlatformService::BINANCE, $ticker = 'BTCUSDT', $orderId = '')
     {
          if (empty($orderId)) {
               return self::ORDER_STATUS_FILLED;
          }
          if ($platform == PlatformService::BINANCE) {
               $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
               $status = $api->orderStatus($ticker, $orderId);
               return $status['status'];
          }
          return self::ORDER_STATUS_FILLED;
     }

     //获取某币种当前价格
     public static function getOnePrice($platform = PlatformService::BINANCE, $ticker = 'BTCUSDT')
     {
          if ($platform == PlatformService::BINANCE) {
               $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
               $prices = $api->prices();
               return $prices[$ticker];
          }
          return 0;
     }

     // 币值格式化 btc卖价
     public static function coinShow($num, $decPlace = 6)
     {
//          if (empty($num)) {
//               return number_format(0, $decPlace, '.', '');
//          }
          $numDeal = floor($num * pow(10,6)) / pow(10, 6);
          return number_format($numDeal, $decPlace, '.', '');
     }

     // 币价格式化 usdt买价
     public static function coinShowUsdt($num, $decPlace = 2)
     {
          $numDeal = ceil($num * pow(10,2)) / pow(10, 2);
          return number_format($numDeal, $decPlace, '.', '');
     }

}