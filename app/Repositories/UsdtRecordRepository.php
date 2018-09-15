<?php

namespace App\Repositoties;

use App\Models\UsdtRecord;

class UsdtRecordRepository
{
    public static function createUsdtLeftRecord($key, $coin = 'eos', $price, $usdtLeft, $timestamp)
    {
        $insert = [
            'key' => substr($key, 0, 6),
            'coin' => $coin,
            'price' => $price,
            'usdt_left' => $usdtLeft,
            'timestamp' => $timestamp
        ];
        UsdtRecord::insert($insert);
    }
}