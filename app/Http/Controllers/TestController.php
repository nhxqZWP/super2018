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
          $data = OrderService::getOnePrice();
          dd($data);
//          Redis::set('test', -2);
//          $a = Redis::get('test');
//          dd($a == -2);
          RunService::runOne();
          dd('end');
     }
}