<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Service\RunService;
use League\Flysystem\Config;

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
          RunService::runOne();
          dd(Config('run'));
     }
}