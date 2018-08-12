<?php

return [
     'get_platform' => 'binance',
     'get_platform_coin' => 'BTC/USDT',
     'get_platform_key' => env('binance_key'),
     'get_platform_secret' => env('binance_secret'),
     'do_trade' => [
          'binance' => [
               'symbol' => 'BTC/USDT',
               'key' => env('binance_key'),
               'secret' => env('binance_secret')
          ]
     ]
];