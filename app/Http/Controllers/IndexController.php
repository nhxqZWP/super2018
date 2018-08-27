<?php

namespace App\Http\Controllers;

use App\Platforms\Binance;
use App\Services\PlatformService;
use App\Services\StrategyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{

     public function getIndex()
     {
          $key = StrategyService::THREE_DOWN_BTCUSDT;
          $status = Redis::get($key);

          $data = [];
          $doAccount = Config('run')['do_trade'];
          $i = 1;
          foreach ($doAccount as $plat => $account) {
               if (!empty($account['key'])) {
                    $api = new Binance($account['key'], $account['secret']);
                    $balance = $api->balances();
                    if ($i==2) {
                         dd($balance);
                    }
                    $coin1 = $balance['BTC'];
                    $coin1Str = '';
                    foreach ($coin1 as $k => $c) {
                         $coin1Str .= $k .':'.$c."<br>";
                    }
                    $coin2 = $balance['USDT'];
                    $coin2Str = '';
                    foreach ($coin2 as $k => $c) {
                         $coin2Str .= $k .':'.$c."<br>";
                    }
                    $data[] = ['status' => $status, 'coin1' => $coin1Str, 'coin2' => $coin2Str];
               }
               $i++;
          }
          return view('index', ['data' => $data]);

//          $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
//          $exchangeInfo =  $api->exchangeInfo()['symbols'];
//          $info = [];
//          foreach ($exchangeInfo as $fo) {
//               if ($fo['symbol'] == 'BTCUSDT') $info = $fo;
//          }

//          return view('index', ['status' => $status, 'info' => $info, 'coin1' => $coin1Str, 'coin2' => $coin2Str]);
     }

     public function getSwitch(Request $request)
     {
          $switch = $request->get('action');
          $key = $request->get('key');
          Redis::set($key, $switch);
          return redirect('/');
     }
}