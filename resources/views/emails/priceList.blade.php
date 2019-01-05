<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
　
<head>
    　　
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    　　<title>合约价格报告</title>
    　　
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body style="width: 602px;">
<table cellpadding="0" cellspacing="0" border="1" align="center" style="width:600px">
    <tr>
        <td>
            <table cellpadding="0" cellspacing="0" border="1" align="center">
                <tr>
                    <td>合约</td>
                    <td>价格</td>
                </tr>
                @foreach($priceList as $coin => $price)
                <tr>
                    <td width="200" valign="top">
                        {{$coin}}
                    </td>
                    <td width="200" valign="top">
                        {{$price}}
                    </td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>

    <tr>
        <td>
            <table cellpadding="0" cellspacing="0" border="1" align="center">
                <tr>
                    <td>合约</td>
                    <td>价格差</td>
                </tr>
                <tr>
                    <td width="200" valign="top">
                        XBTUSD-XBTH19
                    </td>
                    <td width="200" valign="top">
                        {{$priceList['XBTUSD']-$priceList['XBTH19']}}
                    </td>
                </tr>
                <tr>
                    <td width="200" valign="top">
                        XBTH19-XBTM19
                    </td>
                    <td width="200" valign="top">
                        {{$priceList['XBTH19']-$priceList['XBTM19']}}
                    </td>
                </tr>
                <tr>
                    <td width="200" valign="top">
                        XBTUSD-XBTM19
                    </td>
                    <td width="200" valign="top">
                        {{$priceList['XBTUSD']-$priceList['XBTM19']}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td>
            <table cellpadding="0" cellspacing="0" border="1" align="center">
                <tr>
                    <td>时间</td>
                    <td>Rate</td>
                    <td>RateDaily</td>
                </tr>
                @foreach($rateList as $rate)
                    <tr>
                        <td width="200" valign="top">
                            {{$rate['timestamp']}}
                        </td>
                        <td width="200" valign="top">
                            {{$rate['fundingRate'] * 100}}%
                        </td>
                        <td width="200" valign="top">
                            {{$rate['fundingRateDaily'] * 100}}%
                        </td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
</body>

</html>