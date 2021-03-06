<?php

return [
     'get_platform' => 'binance',
     'get_platform_coin' => 'BTC/USDT',
     'get_platform_key' => env('BINANCE_KEY'),
     'get_platform_secret' => env('BINANCE_SECRET'),
     'do_trade' => [
          'binance' => [
               'symbol' => 'EOS/USDT',
               'key' => env('BINANCE_KEY', ''),
               'secret' => env('BINANCE_SECRET', '')
          ],
          'binance ' => [
               'symbol' => 'EOS/USDT',
               'key' => env('BINANCE_KEY2', ''),
               'secret' => env('BINANCE_SECRET2', '')
          ]
     ],
];