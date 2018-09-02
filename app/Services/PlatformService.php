<?php

namespace App\Services;

use App\Platforms\Binance;
use Illuminate\Support\Facades\Redis;

class PlatformService
{
     const BINANCE = 'binance';
     const HUOBI = 'huobi';
     const BITMEX = 'bitmex';
     const OKEX = 'okex';

     static function BinanceGetKey()
     {
          return Config('run')['get_platform_key'];
     }

     static function BinanceGetSecret()
     {
          return Config('run')['get_platform_secret'];
     }

     static function getLowestPriceSinceKey($ticker = 'EOSUSDT', $period = '12h', $platform = self::BINANCE)
     {
          return $platform . $ticker . $period . 'lowest';
     }

     static function delLowestPriceSince($ticker = 'EOSUSDT', $period = '12h', $platform = self::BINANCE)
     {
          $key = self::getLowestPriceSinceKey($ticker, $period, $platform);
          Redis::del($key);
     }

     static function setLowestPriceSince($ticker = 'EOSUSDT', $period = '12h', $platform = self::BINANCE)
     {
          if ($platform == self::BINANCE) {
               $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
               $data = $api->candlesticks($ticker, $period);
               $last = array_reverse($data)[1];

               $timeStampKey = $platform . $ticker . $period . 'timestamp';
               $timeStampSave = Redis::get($timeStampKey);
//               if (is_null($timeStampSave)) {
//                    Redis::set($timeStampKey, $last['openTime']);
//                    return null;
//               }
               if ($timeStampSave == $last['openTime']) return null;
               Redis::set($timeStampKey, $last['openTime']);

               $lowest = $last['low'];
               $key = self::getLowestPriceSinceKey($ticker, $period, $platform);
               Redis::set($key, $lowest);
          }
     }

     static function getIsLowerThanLowestPrice($price, $ticker = 'EOSUSDT', $period = '12h', $platform = self::BINANCE)
     {
          $key = self::getLowestPriceSinceKey($ticker, $period, $platform);
          $markPrice = Redis::get($key);
          if (is_null($markPrice)) return false;

          if ($price < $markPrice) {
               return true;
          } else {
               return false;
          }
     }

}