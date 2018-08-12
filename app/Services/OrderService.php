<?php

namespace App\Service;

class OrderService
{
     const OPE_BUY = 0;  //买单操作
     const OPE_SELL = 1; //卖单操作

     //以买单最高下买单 以卖单最低下卖单
    public function placeNormalOrder($ope = null, $platform = null, $symbol = null, $amountPercent = 1, $mark = '')
    {
        if (is_null($ope) || is_null($platform) || is_null($symbol)) {
            return null;
        }

        if ($ope === self::OPE_BUY) {

        } elseif ($ope == self::OPE_SELL) {

        }

        return null;
    }

    //市场价下单
    public function placeMarketOrder($ope = null, $platform = null, $symbol = null)
    {
         if (is_null($ope) || is_null($platform) || is_null($symbol)) {
              return null;
         }
    }
}