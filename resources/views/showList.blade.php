<!doctype html>
<html>
　
<head>
    　　
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    　　<title>合约价格记录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
<div class="container">
    <table cellpadding="0" cellspacing="0" border="1" align="center">
        <tr>
            <td>XBTUSD</td>
            <td>XBTH19</td>
            <td>XBTM19</td>
            <td>XBTUSD-XBTH19</td>
            <td>XBTH19-XBTM19</td>
            <td>time</td>
        </tr>
        @foreach ($list as $item)
            <tr>
                <td>
                    {{$item->XBTUSD}}
                </td>
                <td>
                    {{$item->XBTH19}}
                </td>
                <td>
                    {{$item->XBTM19}}
                </td>
                <td>
                    {{$item->XBTUSD_XBTH19}}
                </td>
                <td>
                    {{$item->XBTH19_XBTM19}}
                </td>
                <td>
                    {{$item->created_at}}
                </td>
            </tr>
        @endforeach
    </table>
</div>

{{ $list->links() }}

</body>
</html>