<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\RunService;
use Illuminate\Support\Facades\Redis;

/**
 * Created by PhpStorm.
 * User: zhangweipeng
 * Date: 2018/8/12
 * Time: 下午1:56
 */

class TestController extends Controller
{
     public function test()
     {
//          $endSecond =  [
//                   "open" => "6299.49000000",
//                   "high" => "6309.09000000",
//                   "low" => "6297.32000000",
//                   "close" => "6306.01000000",
//                   "volume" => "192033.73528155",
//                   "openTime" => 1534086270000,
//                   "closeTime" => 1534086299999,
//                   "assetVolume" => "30.45977900",
//                   "baseVolume" => "192033.73528155",
//                   "trades" => 124,
//                   "assetBuyVolume" => "20.72199400",
//                   "takerBuyVolume" => "130628.70280336",
//                   "ignored" => "0"
//                 ];
//          $quantity = 0;
//          $doAccount = Config('run')['do_trade'];
//          foreach ($doAccount as $plat => $account) {
//               list($orderRes, $buyPrice, $orderId, $quantity) = OrderService::placeBuyOrderByCurrentPrice($plat, $account['symbol'], $account['key'], $account['secret']);
//               if (!is_null($orderRes)) {
//                    return $orderRes;
//               }
//          }
//
//          $data = OrderService::getOnePrice();
//          dd($data);
////          Redis::set('test', -2);
////          $a = Redis::get('test');
////          dd($a == -2);
//          RunService::runOne();
//          dd('end');
     }
}