<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>CocoPro</title>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center;margin-top: 1%">
            MACD改进策略 状态：
            @if(is_null($data['status']) || $data['status'] == 0)
                <span style="color:#c7254e">已关闭</span>
            @else
                <span style="color:#67b168">运行中</span>
            @endif
            &nbsp;
            @if(is_null($data['status']) || $data['status'] == 0)
                <a href="/switch?key=three_down_btcusdt_minute&action=1" class="btn btn-success btn-sm" role="button">&nbsp;打开&nbsp;</a>
            @else
                <a href="/switch?key=three_down_btcusdt_minute&action=0" class="btn btn-danger btn-sm" role="button">&nbsp;关闭&nbsp;</a>
            @endif
        </div>
    </div>
    <div class="row">
        @foreach($data['list'] as $item)
        <div class="col-md-6" style="text-align: center;margin-top: 1%">
            {{--<table class="table table-striped">--}}
                {{--<tr>--}}
                    {{--<td>策略</td>--}}
                    {{--<td>状态</td>--}}
                    {{--<td>操作</td>--}}
                {{--</tr>--}}
                {{--<tr>--}}
                    {{--<td>MACD改进策略</td>--}}
                    {{--<td>--}}
                        {{--@if(is_null($item['status']) || $item['status'] == 0)--}}
                            {{--已关闭--}}
                            {{--@else--}}
                            {{--运行中--}}
                        {{--@endif--}}
                    {{--</td>--}}
                    {{--<td>--}}
                        {{--@if(is_null($item['status']) || $item['status'] == 0)--}}
                            {{--<a href="/switch?key=three_down_btcusdt_minute&action=1" class="btn btn-success btn-sm" role="button">&nbsp;打开&nbsp;</a>--}}
                        {{--@else--}}
                            {{--<a href="/switch?key=three_down_btcusdt_minute&action=0" class="btn btn-danger btn-sm" role="button">&nbsp;关闭&nbsp;</a>--}}
                        {{--@endif--}}
                    {{--</td>--}}
                {{--</tr>--}}
            {{--</table>--}}
            {{--<br>--}}
            <table class="table table-striped">
                <tr>
                    <td>EOS</td>
                    <td>USDT</td>
                    <td>BNB</td>
                </tr>
                <tr>
                    <td><?php echo $item['coin4']; ?></td>
                    <td><?php echo $item['coin2']; ?></td>
                    <td><?php echo $item['coin3']; ?></td>
                </tr>
            </table>
        </div>
        @endforeach
    </div>

    <div class="row" style="color: #FFFFFF">
        @foreach($data['list'] as $item)
            <div class="col-md-6" style="text-align: center;margin-top: 1%">
                <table class="table table-striped">
                    <tr style="color: #000000">
                        <td>type</td>
                        <td>price</td>
                        <td>quantity</td>
                        <td>commission</td>
                        <td>asset</td>
                        <td>time</td>
                    </tr>
                    @foreach($item['history'] as $e)
                    <tr>
                        <td bgcolor="{{$e['isBuyer'] == true ? '#ff1493' : '#00ced1'}}">{{$e['isBuyer'] == true ? 'buy' : 'sell'}}</td>
                        <td bgcolor="{{$e['isBuyer'] == true ? '#ff1493' : '#00ced1'}}">{{$e['price']}}</td>
                        <td bgcolor="{{$e['isBuyer'] == true ? '#ff1493' : '#00ced1'}}">{{$e['qty']}}</td>
                        <td bgcolor="{{$e['isBuyer'] == true ? '#ff1493' : '#00ced1'}}">{{$e['commission']}}</td>
                        <td bgcolor="{{$e['isBuyer'] == true ? '#ff1493' : '#00ced1'}}">{{$e['commissionAsset']}}</td>
                        <td bgcolor="{{$e['isBuyer'] == true ? '#ff1493' : '#00ced1'}}">{{date('m-d H:i:s', ceil($e['time']/1000))}}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        @endforeach
    </div>

    {{--<div class="row">--}}
        {{--<div class="col-md-6" style="text-align: center;margin-top: 2%">--}}
            {{--{{print_r($data['BTC'])}}--}}
        {{--</div>--}}
        {{--<div class="col-md-6" style="text-align: center;margin-top: 2%">--}}
            {{--{{print_r($data['EOS'])}}--}}
        {{--</div>--}}
    {{--</div>--}}

</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>