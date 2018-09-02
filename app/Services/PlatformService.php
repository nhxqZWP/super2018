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

     static function getLowestPriceSinceKey($ticker, $period = '12h', $platform = self::BINANCE)
     {
          return $platform . $ticker . $period . 'lowest';
     }

     static function markLowestPriceSinceDo($ticker, $period = '12h', $platform = self::BINANCE)
     {
          $key = $platform . $ticker . $period . 'lowest_do';
          Redis::set($key);
     }

     static function setLowestPriceSince($ticker, $period = '12h', $platform = self::BINANCE)
     {
          if ($platform == self::BINANCE) {
               $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
               $data = $api->candlesticks($ticker, $period);
               $lowest = array_reverse($data)[1]['low'];
               $key = self::getLowestPriceSinceKey($ticker, $period, $platform);
               Redis::set($key, $lowest);
          }
     }


}