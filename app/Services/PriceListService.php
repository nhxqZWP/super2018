<?php
/**
 * Created by PhpStorm.
 * User: a17
 * Date: 2019/1/5
 * Time: 下午6:38
 */

namespace App\Services;


use App\Mail\PriceList;
use App\Platforms\Bitmex;
use App\PriceRecord;
use Illuminate\Support\Facades\Mail;

class PriceListService
{
    public static function getList()
    {
        $bitMex = Bitmex::instance();
        $arr = ['XBTUSD','XBTH19','XBTM19'];
        $priceList = [];
        foreach ($arr as $symbol) {
            $priceList[$symbol] = $bitMex->getTicker($symbol)['last'];
        }

//        $priceList = [
//            'XBTUSD' => 3829.5,
//            'XBTH19' => 3731.5,
//            'XBTM19' => 3703.5
//        ];
//        $this->rateList = $bitMex->getFunding('XBTUSD');
        $record = new PriceRecord();
        $record->XBTUSD = $priceList['XBTUSD'];
        $record->XBTH19 = $priceList['XBTH19'];
        $record->XBTM19 = $priceList['XBTM19'];
        $record->XBTUSD_XBTH19 = $priceList['XBTUSD'] - $priceList['XBTH19'];
        $record->XBTH19_XBTM19 = $priceList['XBTH19'] - $priceList['XBTM19'];
        $record->XBTUSD_XBTM19 = $priceList['XBTUSD'] - $priceList['XBTM19'];

        //通知
        if ($record->XBTH19_XBTM19 < 15 || $record->XBTH19_XBTM19 > 40) {
            Mail::to(['631849184@qq.com'])->send(new PriceList());
        }


        $record->save();

    }

    public static function showList()
    {
        date_default_timezone_set('PRC');
        $list = PriceRecord::paginate(100);
        return view('showList', ['list' => $list]);
    }
}