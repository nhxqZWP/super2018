<?php

namespace App\Services;

use Binance;

class OrderService
{
     const OPE_BUY = 0;  //买单操作
     const OPE_SELL = 1; //卖单操作

     const ORDER_STATUS_NEW = 10;
     const ORDER_STATUS_PARTIALLY_FILLED = 11;
     const ORDER_STATUS_FILLED = 12;
     const ORDER_STATUS_CANCELED= 13;
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

    //市场价下单
    public static function placeMarketOrder($ope = null, $platform = null, $symbol = null)
    {
         if (is_null($ope) || is_null($platform) || is_null($symbol)) {
              return null;
         }
    }

    //获取订单状态
    public static function getOrderStatus($platform = PlatformService::BINANCE, $ticker = 'BTCUSDT',  $orderId = '')
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
              dd($prices);
              return $prices[$ticker];
         }
    }
}