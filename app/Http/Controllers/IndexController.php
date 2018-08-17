<?php

namespace App\Http\Controllers;

use App\Services\StrategyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{

     public function getIndex()
     {
          $key = StrategyService::THREE_DOWN_BTCUSDT;
          $status = Redis::get($key);
          return view('index', ['status' => $status]);
     }

     public function getSwitch(Request $request)
     {
          $switch = $request->get('action');
          $key = $request->get('key');
          Redis::set($key, $switch);
          return redirect('/');
     }
}