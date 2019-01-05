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
                <th>
                    <td>合约</td>
                    <td>价格</td>
                </th>
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

            {{--<a href="http://htmlemailboilerplate.com" target ="_blank" title="Styling Links" style="color: orange; text-decoration: none;">Coloring Links appropriately</a>--}}
        </td>
    </tr>
</table>
</body>

</html>