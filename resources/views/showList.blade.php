<!doctype html>
<html>
　
<head>
    　　
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    　　<title>合约价格记录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    {!! Charts::styles() !!}
</head>

<body>
<div class="container">
    <div class="row">
        {!! $chart->html() !!}
    </div>
    <div class="row">
        <table cellpadding="0" cellspacing="0" border="1" align="center" class="table table-striped">
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
        {{ $list->links() }}
    </div>


</div>
{!! Charts::scripts() !!}
{!! $chart->script() !!}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</body>
</html>
