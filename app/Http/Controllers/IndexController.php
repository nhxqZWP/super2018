<?php

namespace App\Http\Controllers;

use App\Charts\SampleChart;
use App\Mail\PriceList;
use App\Platforms\Binance;
use App\Platforms\Bitmex;
use App\PriceRecord;
use App\Services\OrderService;
use App\Services\PlatformService;
use App\Services\PriceListService;
use App\Services\StrategyService;
use App\Services\TargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    public function getIndex()
    {
        date_default_timezone_set('PRC');
        $list = PriceRecord::paginate(10);

        $chart = new SampleChart;
        $chart->title('合约差价变动')
            ->labels($list->pluck('created_at'))  //行
            ->dataset('XBTH19-XBTM19','line',$list->pluck('XBTH19_XBTM19')) //列
            ->dataset('XBTH19-XBTM19','line',$list->pluck('XBTH19_XBTM19')); //列
//            ->responsive(false);

        return view('showList', ['list' => $list, 'chart' => $chart]);


        PriceListService::showList();
//        PriceListService::getList();
        dd(1);

        Mail::to(['631849184@qq.com','aimococo@163.com'])->send(new PriceList());
dd('ok');
        return view('emails.priceList');
        $bitMex = Bitmex::instance();
//        $ticker = $bitMex->getTicker('XBTH19');
        $arr = ['XBTUSD','XBTH19','XBTM19'];
//        $res = [];
//        foreach ($arr as $symbol) {
//            $res[$symbol] = $bitMex->getTicker($symbol)['last'];
//        }
//        dd($res);
        $res = $bitMex->getFunding('XBTUSD');
        dd($res);
        return view('welcome');
    }

     public function getIndex2()
     {
//          $doAccount = Config('run')['do_trade'];
//          foreach ($doAccount as $plat => $account) {
//               if (!empty($account['key'])) {
//                    list($orderRes, $quantity, $orderId) = OrderService::placeSellOrderByCurrentPrice(trim($plat), $account['symbol'], $account['key'], $account['secret']);
//               }
//          }
//          dd('ok');

          date_default_timezone_set('PRC');
          $key = StrategyService::THREE_DOWN_BTCUSDT;
          $status = Redis::get($key);

          $data['status'] = $status;
          $data['list'] = [];
          $doAccount = Config('run')['do_trade'];
          foreach ($doAccount as $plat => $account) {
               if (!empty($account['key'])) {
                         $api = new Binance($account['key'], $account['secret']);

                         $balance = $api->balances();
                         $coin1 = $balance['BTC'];
                         $coin1Str = '';
                         foreach ($coin1 as $k => $c) {
                              if($k == 'btcValue') break;
                              $coin1Str .= $k .':'.$c."<br>";
                         }
                         $coin2 = $balance['USDT'];
                         $coin2Str = '';
                         foreach ($coin2 as $k => $c) {
                              if($k == 'btcValue') break;
                              $coin2Str .= $k .':'.$c."<br>";
                         }
                         $coin3 = $balance['BNB'];
                         $coin3Str = '';
                         foreach ($coin3 as $k => $c) {
                              if($k == 'btcValue') break;
                              $coin3Str .= $k .':'.$c."<br>";
                         }
                         $coin4 = $balance['EOS'];
                         $coin4Str = '';
                         foreach ($coin4 as $k => $c) {
                              if($k == 'btcValue') break;
                              $coin4Str .= $k .':'.$c."<br>";
                         }

                    $api = new Binance($account['key'], $account['secret']);
                    $history = $api->history('EOSUSDT', 50);

                    $data['list'][] = ['status' => $status, 'history' => array_reverse($history),'coin1' => $coin1Str, 'coin2' => $coin2Str, 'coin3' => $coin3Str, 'coin4' => $coin4Str];
               }
          }

//          $api = new Binance(PlatformService::BinanceGetKey(), PlatformService::BinanceGetSecret());
//          $exchangeInfo =  $api->exchangeInfo()['symbols'];
//          $infoBTC = []; $infoEOS = [];
//          foreach ($exchangeInfo as $fo) {
//               if ($fo['symbol'] == 'BTCUSDT') $infoBTC = $fo;
//               if ($fo['symbol'] == 'EOSUSDT') $infoEOS = $fo;
//          }
//          $data['BTC'] = $infoBTC;
//          $data['EOS'] = $infoEOS;

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