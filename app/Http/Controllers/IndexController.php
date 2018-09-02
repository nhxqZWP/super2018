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
          $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
          $data = $api->candlesticks('EOSUSDT', '12h');
          dd(array_reverse($data));

          $key = StrategyService::THREE_DOWN_BTCUSDT;
          $status = Redis::get($key);

          $data['list'] = [];
          $doAccount = Config('run')['do_trade'];
          foreach ($doAccount as $plat => $account) {
               if (!empty($account['key'])) {
                         $api = new Binance($account['key'], $account['secret']);
                         $balance = $api->balances();
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
                         $coin3 = $balance['BNB'];
                         $coin3Str = '';
                         foreach ($coin3 as $k => $c) {
                              $coin3Str .= $k .':'.$c."<br>";
                         }
                         $data['list'][] = ['status' => $status, 'coin1' => $coin1Str, 'coin2' => $coin2Str, 'coin3' => $coin3Str];
               }
          }

          $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
          $exchangeInfo =  $api->exchangeInfo()['symbols'];
          $infoBTC = []; $infoEOS = [];
          foreach ($exchangeInfo as $fo) {
               if ($fo['symbol'] == 'BTCUSDT') $infoBTC = $fo;
               if ($fo['symbol'] == 'EOSUSDT') $infoEOS = $fo;
          }
          $data['BTC'] = $infoBTC;
          $data['EOS'] = $infoEOS;

          return view('index', ['data' => $data]);

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