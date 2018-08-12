<?php

namespace App\Service;

use App\Constants\ExamCardsConstant;
use Illuminate\Support\Facades\Log;

class RunService
{
     //黑三兵后买 固定偏移卖
     public static function runOne()
     {
          $config = config('run');
          $getPlatform = $config['get_platform'];
          $getPlatformCoin = $config['get_platform_coin'];
          $trades = $config['do_trade'];

          // 获取交易触发
          $getOpe = StrategyService::BlackThree($getPlatform, $getPlatformCoin);
          if (!is_null($getOpe)) {
               // 进行交易
               foreach ($trades as $plat => $coin) {
                    OrderService::placeNormalOrder($getOpe, $plat, $coin);
               }
          }

          return null;
     }
}