<?php

namespace App\Service;

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
}