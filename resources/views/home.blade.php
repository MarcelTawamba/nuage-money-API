@extends('layouts.app')

@section('content')
    @push('third_party_stylesheets')
        @include('layouts.datatables_css')
    @endpush
    @include('flash_message')
    <div class="container-fluid  " style="background-color: white">
        @if(\Illuminate\Support\Facades\Auth::user()->is_admin)
        <div class="d-flex flex-wrap">

                <div class="top-card bg-custom-blue">

                    <p class="text-center mb-0 h3">{{count($companies)}}</p>
                    <p class="text-center m-0">Company</p>
                </div>
                <div class="top-card">
                    <p class="text-center mb-0 h3">{{count($clients)}}</p>
                    <p class="text-center m-0">Apps</p>
                </div>
                <div class="top-card">
                    <p class="text-center mb-0 h3">{{count($achat)}}</p>
                    <p class="text-center m-0">Transaction</p>
                </div>
                <div class="top-card">
                    <p class="text-center mb-0 h3">{{count($users)}}</p>
                    <p class="text-center m-0">Users</p>
                </div>

                <div class="top-card">
                    <p class="text-center mb-0 h3">{{count($wallets)}}</p>
                    <p class="text-center m-0">Wallets</p>
                </div>


        </div>
        <br/>
        <div class="d-flex flex-wrap">
            @foreach($wallets as $wallet)

                <div class="custom-card">
                <div class="img-div"><img src="{{url('images/money.png')}}"></div>
                    @if(\Illuminate\Support\Facades\Auth::user()->is_admin)
                        <p class="mt-3 ml-3 mb-1 text-dark-blue">
                            Available Balance
                        </p>
                    @else
                        <p class="mt-3 ml-3 mb-1 text-dark-blue">
                            {{$wallet->user->client->name}}
                        </p>
                    @endif
                <p class="ml-3 h4 mb-3 text-custom-blue">{{$wallet->currency->name}} {{$wallet->balance}} </p>
                <p class="ml-3 mb-2 text-dark-blue">Total pay-ins: <span class="text-custom-blue">{{$wallet->currency->name}} {{$wallet->sumPayIn()}}</span></p>
                <p class="ml-3 text-dark-blue">Total payouts: <span class="text-custom-blue">{{$wallet->currency->name}} {{$wallet->sumPayOut()}}</span></p>

            </div>
            @endforeach
        </div>
        @else
        <div class="d-flex flex-wrap">
            @if(\Illuminate\Support\Facades\Auth::user()->account_type =="company")
                <div class="mb-4 mb-md-0">
                    <div class="client-card m-md-4 client-card-color-0">
                        <img class="" src="{{url("images/pattern0.png")}}">
                        <div class="m-3 client-card-content">
                            <div class="">
                                <div class="d-flex mr-5 mb-4" style="align-items: center;">
                                    <p class="h1 mb-0 ml-2">{{ $clients[0]->name}}</p>
                                </div>

                                <div class="d-flex mr-5 mb-3" style="align-items: center">
                                    <p class="h6 mb-0">Company&nbsp;:</p>
                                    <p class="h6 mb-0 ml-2">{{ $clients[0]->company->name }}</p>
                                </div>


                                <div class="d-flex mr-5 mb-3" style="align-items: center;">
                                    <p class="h6 mb-0">Redirect&nbsp;:</p>
                                    <p class="h6 mb-0 ml-2">{{ $clients[0]->redirect }}</p>
                                </div>

                                <div class="d-flex mr-5 mb-3" style="align-items: center;">
                                    <p class="h6 mb-0">client_id&nbsp;:</p>
                                    <p class="h6 mb-0 ml-2">{{ $clients[0]->id }}</p>
                                </div>

                                <div class="d-flex mr-5 mb-4" style="align-items: center;">
                                    <p class="h6 mb-0">Secret&nbsp;:</p>

                                    @if(Session::has('secret'))
                                        <p class="h6 mb-0 ml-2 secret">{{ Session::get('secret')}}</p>
                                    @else

                                        <p class="h6 mb-0 ml-2 secret">******************************</p>
                                    @endif
                                </div>
                                <div class="d-flex">
                                    @if(Session::has('secret'))
                                        <a href="{{route('apps.show',$clients[0]->id)}}"  class="btn btn-secondary mr-2 show-button" >Hide Secret</a>
                                        {{ session()->forget('secret') }}
                                    @else
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-secondary mr-2 show-button" data-toggle="modal" data-target="#exampleModalCenter">
                                            Show Secret
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <form method="POST" action="{{route('apps.show_secret',$clients[0]->id)}}" >
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-dark-blue" id="exampleModalLongTitle">Enter password to show password</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">

                                                            <input name="pass" type="password" class="form-control" placeholder="Password" />
                                                        </div>
                                                        <div class="modal-footer">

                                                            <button type="submit" class="btn btn-primary">Show</button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    @endif

                                    <a href="{{ route('apps.edit', [$clients[0]->id]) }}"
                                       class='btn bg-warning  mr-2'>
                                        <i class="fa fa-cog mr-1"></i>Setting
                                    </a>

                                    <form method='post' action="{{route('apps.generate',$clients[0]->id)}}" >
                                        @csrf
                                        <button class="btn btn-primary mr-2" onclick= "return confirm('Are you sure you want to regenerate the secret?')">Regenerate Secret</button>

                                    </form>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <div class="d-flex flex-wrap my-md-4 ">
                @foreach($clients[0]->wallets() as $wallet)

                    <div class="custom-card @if ( $clients[0]->main_wallet == $wallet->currency->name) active @endif ">
                        @if ( $clients[0]->main_wallet == $wallet->currency->name) <div class="img-div"><img src="{{url('images/money.png')}}"></div>@endif
                        <p class="mt-3 ml-3 mb-1 text-dark-blue">
                             @if ( $clients[0]->main_wallet == $wallet->currency->name) Main Wallet @else Available Balance @endif
                        </p>
                        <p class="ml-3 h4 mb-3 text-custom-blue">{{$wallet->currency->name}} {{$wallet->balance}} </p>
                        <p class="ml-3 mb-2 text-dark-blue">Total pay-ins: <span class="text-custom-blue">{{$wallet->currency->name}} {{$wallet->sumPayIn()}}</span></p>
                        <p class="ml-3 text-dark-blue">Total payouts: <span class="text-custom-blue">{{$wallet->currency->name}} {{$wallet->sumPayOut()}}</span></p>

                    </div>
                @endforeach
            </div>
        </div>
        <br/>
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->is_admin)
            <a href="{{ route('wallets.index') }}" class="btn ml-2 bg-custom-blue ">View all wallets  <i class="ml-1 fas fa-arrow-alt-circle-right"></i></a>
            @else
                <a href="{{ route('apps.withdraw', [$clients[0]->id]) }}"
                   class='btn ml-2 bg-custom-blue mb-2'>
                    Send Fund
                    <i class="ml-1 fas fa-share-square"></i>
                </a>
                <a href="{{ route('exchange-request.create', [$clients[0]->id]) }}"
                   class='btn ml-2 bg-custom-blue mb-2'>
                    Convert Fund
                    <i class="ml-1 fas fa-sync"></i>
                </a>
                <a href="{{ route('apps.fund_wallet', [$clients[0]->id]) }}"
                   class='btn ml-2 bg-custom-blue mb-2'>
                    Fund Wallet
                    <i class="ml-1 far fa-credit-card"></i>
                </a>


                <button class="btn bg-warning  ml-2 show-button mb-2" data-toggle="modal" data-target="#exampleModalCenters">
                    Change Main Wallet
                    <i class="fa fa-cog mr-1"></i>
                </button>
                <form method="POST" action="{{route('apps.change_wallet' )}}" >
                    @csrf
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModalCenters" tabindex="-1" role="dialog" aria-labelledby="exampleModalCentersTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">

                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title text-dark-blue" id="exampleModalLongTitle">Change Main Wallet</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">

                                    <!-- Company Type Field -->
                                    <div class="form-group ">



                                        <select class="form-control custom-select" name="main_wallet">
                                            @foreach($walls as $i=>$wal)
                                                <option value="{{$i}}" @if($clients[0]->main_wallet == $wal) selected @endif> {{$wal}} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">

                                    <button type="submit" class="btn btn-primary">save</button>
                                </div>
                            </div>


                        </div>
                    </div>
                </form>

            @endif
        <div class="mt-2">
            <h5 class="text-center">All Transaction</h5>

            <select class="m-2 select" style="padding: 10px;  border:none;">
                <option>Last 30 days&nbsp;</option>
                <option>day&nbsp;</option>
                <option>month&nbsp;</option>
                <option>semester&nbsp;</option>
                <option>year&nbsp;</option>

            </select>

            <div class="row ">
                <div class="chart-card col-md-8 ">
                    <div class="chart chart-sm">
                        <div id="chartdiv"></div>
                    </div>

                </div>
                <div class="chart-card col-md-3">
                    <p class="h5 mt-3 mx-2 mb-1">Success Rate</p>
                    <div class="app-card-content">
                        <div class="chart chart-sm">
                            <div id="failed_stat"></div>
                        </div>
                    </div>

                </div>
            </div>
            <br/>
            <div class="table-responsive p-2 p-lg-5 mb-5 ">
                <div class="">
                    <table class="table table-striped table-bordered dataTable " id="transactions-table">
                        <thead class="bg-custom-blue">
                        <tr>
                            <th>Reference</th>
                            <th>Wallet</th>
                            <th>Amount</th>
                            <th>Balance Before</th>
                            <th>Balance After</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->reference }}</td>
                                <td>   @if($transaction->wallet->user->name !=null)
                                        {{ $transaction->wallet->user->name}}
                                    @endif
                                    {{ $transaction->wallet->currency->name }}</td>
                                <td>{{ $transaction->amount }}</td>
                                <td>{{ $transaction->balance_before }}</td>
                                <td>{{ $transaction->balance_after }}</td>
                                <td>{{ $transaction->created_at->format('d M, Y')  }}</td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-row-reverse">
                    <a href="{{ route('transactions.index') }}" class="btn ml-2 bg-custom-blue ">View all transaction  <i class="ml-1 fas fa-arrow-alt-circle-right"></i></a>

                </div>

            </div>



        </div>

    </div>
    <style>
        .top-card{

            width: 150px;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background-color: #6EAFFB66;
            margin: 8px 10px;
            color:  #2B5587;
        }

        .chart-card{
            margin: 15px 15px;
            border-radius: 20px;
            border: 1px solid #D9D6D6;
        }

        .custom-card{
            position: relative;
            border-radius: 20px;
            border: 1px solid #D9D6D6;
            width: 271px;
            height: 170px;
            margin: 15px 15px;

        }
        .img-div img{
            width: 40px;
            height: 40px ;
            position: absolute;
            right: 20px;
            top: 10px;
            border-radius: 20px;

        }

        #chartdiv {
            width: 100%;
            height: 475px;
            max-width:100%
        }
        #failed_stat {
            width: 100%;
            height: 475px;
        }

        .client-card-color-0{
            background-color: #496ecc !important;
        }
        .client-card-color-1{
            background-color: #9517c1 !important ;
        }
        .client-card-color-2{
            background-color: #299e4a !important
        }
        .client-card-color-3{
            background-color: #ed8030 !important
        }
        .custom-card.active{
            background-color: white;
            box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;
        }

        .client-card{

            position: relative;
            min-width: 300px;
            border-radius: 10px;
            height: 250px;
        }
        .client-card > img{

            position: absolute;
            height: 100%;
            width: 100%;
            object-fit: cover;

            top: 0;
            left: 0;
            border-radius: 10px;
        }
        .client-card-content{
            background-color: transparent;
            top: 0;
            position: absolute;
            left: 0;
            z-index: 2;
            color: white;
        }


        .bg-custom-blue {
            background-color: #6EAFFB;
            color: white !important;
        }


        .text-custom-blue{
            color: #6EAFFB;
        }

        .text-dark-blue{
            color: #2B5587;
        }

        .custom-card{
            position: relative;
            border-radius: 20px;
            border: 1px solid #D9D6D6;
            width: 271px;
            height: 170px;
            margin-right: 20px;


        }
        .img-div img{
            width: 40px;
            height: 40px ;
            position: absolute;
            right: 20px;
            top: 10px;
            border-radius: 20px;

        }
        .modal-backdrop {

            z-index: 0;

        }


        .client-card-color-0{
            background-color: #496ecc !important;
        }
        .client-card-color-1{
            background-color: #9517c1 !important ;
        }
        .client-card-color-2{
            background-color: #299e4a !important
        }
        .client-card-color-3{
            background-color: #ed8030 !important
        }

        .client-card{

            position: relative;
            width: 500px;
            border-radius: 10px;
            height: 300px;
            overflow: clip;
        }
        .client-card > img{

            position: absolute;
            height: 100%;
            width: 100%;
            object-fit: cover;

            top: 0;
            left: 0;
            border-radius: 10px;
        }
        .client-card-content{
            background-color: transparent;
            top: 0;
            position: absolute;
            left: 0;
            z-index: 2;
            color: white;
        }
        .client-card .client-card-content p{
            text-overflow: ellipsis;
        }

        @media (max-width: 550px){
            .client-card{
                overflow-x: scroll;
                width: calc(100vw - 50px);
            }
            .custom-card{
                margin-bottom: 10px;
            }
            .client-card{

                height: 330px;
                margin-top: 15px;
            }
        }


    </style>



    <!-- Resources -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

    <!-- Chart code -->
    <script>
        let data = {{ Js::from($trans) }};
        am5.ready(function() {

            data = JSON.parse(data)

            for (let i = 0; i < data.length; i++) {
                data[i]['date']=new Date(data[i]['date']).getTime();
            }

            let root = am5.Root.new("chartdiv");

            root.setThemes([
                am5themes_Animated.new(root)
            ]);

            let chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    pinchZoomX:true
                })
            );


            let cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none"
            }));
            cursor.lineY.set("visible", false);

            let xAxis = chart.xAxes.push(
                am5xy.DateAxis.new(root, {
                    baseInterval: { timeUnit: "day", count: 1 },
                    renderer: am5xy.AxisRendererX.new(root, {}),
                    tooltip: am5.Tooltip.new(root, {}),
                    tooltipDateFormat: "yyyy-MM-dd"
                })
            );

            let yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    maxDeviation:1,
                    renderer: am5xy.AxisRendererY.new(root, {pan:"zoom"})
                })
            );

            createSeries("XAF",root,chart,xAxis,yAxis)
            createSeries("NGN",root,chart,xAxis,yAxis)

            let scrollbar = chart.set("scrollbarX", am5xy.XYChartScrollbar.new(root, {
                orientation: "horizontal",
                height: 60
            }));

            let sbDateAxis = scrollbar.chart.xAxes.push(
                am5xy.DateAxis.new(root, {
                    baseInterval: {
                        timeUnit: "day",
                        count: 1
                    },
                    renderer: am5xy.AxisRendererX.new(root, {})
                })
            );

            let sbValueAxis = scrollbar.chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    renderer: am5xy.AxisRendererY.new(root, {})
                })
            );

            let sbSeries = scrollbar.chart.series.push(
                am5xy.LineSeries.new(root, {
                    valueYField: "amount",
                    valueXField: "date",
                    xAxis: sbDateAxis,
                    yAxis: sbValueAxis
                })
            );

            sbSeries.fills.template.setAll({
                fillOpacity: 0.2,
                visible: true
            });



            sbSeries.data.setAll(data);
            let legend = chart.rightAxesContainer.children.push(am5.Legend.new(root, {
                width: 70,
                paddingLeft: 15,
                height: am5.percent(100)
            }));

// When legend item container is hovered, dim all the series except the hovered one
            legend.itemContainers.template.events.on("pointerover", function(e) {
                let itemContainer = e.target;

                // As series list is data of a legend, dataContext is series
                let series = itemContainer.dataItem.dataContext;

                chart.series.each(function(chartSeries) {
                    if (chartSeries != series) {
                        chartSeries.strokes.template.setAll({
                            strokeOpacity: 0.15,
                            stroke: am5.color(0x000000)
                        });
                    } else {
                        chartSeries.strokes.template.setAll({
                            strokeWidth: 3
                        });
                    }
                })
            })

// When legend item container is unhovered, make all series as they are
            legend.itemContainers.template.events.on("pointerout", function(e) {
                let itemContainer = e.target;
                let series = itemContainer.dataItem.dataContext;

                chart.series.each(function(chartSeries) {
                    chartSeries.strokes.template.setAll({
                        strokeOpacity: 1,
                        strokeWidth: 1,
                        stroke: chartSeries.get("fill")
                    });
                });
            })

            legend.itemContainers.template.set("width", am5.p100);
            legend.valueLabels.template.setAll({
                width: am5.p100,
                textAlign: "right"
            });

// It's is important to set legend data after all the events are set on template, otherwise events won't be copied
            legend.data.setAll(chart.series.values);

            chart.appear(1000, 100);

        }) ;// end am5.ready()

        function createSeries(name,root,chart, xAxis,yAxis) {


            let series= chart.series.push(
                am5xy.LineSeries.new(root, {
                    name: name,
                    xAxis: xAxis,
                    yAxis: yAxis,
                    stacked: true,
                    valueYField: "amount",
                    valueXField: "date",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "[bold]{name} {valueY}"
                    })
                })
            );
            series.fills.template.setAll({
                fillOpacity: 0.2,
                visible: true
            });

            series.strokes.template.setAll({
                strokeWidth: 2
            });


            series.data.setAll(data.filter((elt)=> elt["currency"] === name));
            series.appear(1000);
        }

        let data2 = {{ Js::from($trans_stat) }};
        data2 = JSON.parse(data2)
        am5.ready(function() {

            let root = am5.Root.new("failed_stat");

            let data3 = [];
            for(let i=0;i<data2.length;i++){
                if(data2[i]["status"] === "SUCCESSFUL"){
                    data3.push( {
                        status: data2[i]["status"],
                        total : data2[i]["total"],
                        columnSettings: {
                            fill: am5.color(0xA6D997),
                            stroke: am5.color(0xbabf95)
                        }
                    })

                }else if(data2[i]["status"] === "PENDING"){
                    data3.push({
                        status: data2[i]["status"],
                        total : data2[i]["total"],
                        columnSettings: {
                            fill: am5.color(0x6EAFFB),
                            stroke: am5.color(0xbabf95)
                        }
                    })
                }else if(data2[i]["status"] === "FAILED"){
                    data3.push({
                        status: data2[i]["status"],
                        total : data2[i]["total"],
                        columnSettings: {
                            fill: am5.color(0xFF1919),
                            stroke: am5.color(0xbabf95)
                        }
                    })
                }else if(data2[i]["status"] === "CREATED"){
                    data3.push( {
                        status: data2[i]["status"],
                        total : data2[i]["total"],
                        columnSettings: {
                            fill: am5.color(0xEEDBDB),
                            stroke: am5.color(0xbabf95)
                        }
                    })

                }
            }

            root.setThemes([
                am5themes_Animated.new(root)
            ]);


            let chart = root.container.children.push(am5percent.PieChart.new(root, {
                layout: root.verticalLayout,
                innerRadius: am5.percent(50)
            }));

            let series = chart.series.push(am5percent.PieSeries.new(root, {
                valueField: "total",
                categoryField: "status",
            }));


            series.labels.template.set("visible", false);
            series.ticks.template.set("visible", false);
            series.slices.template.setAll({
                templateField: "columnSettings"
            });
            series.data.setAll( data3);

            let legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15,
            }));

            legend.data.setAll(series.dataItems);

            series.appear(1000, 100);

        }); // end am5.ready()

        function hideSmall(ev) {
            if (ev.target.dataItem.values.value.percent >0) {
                ev.target.hide();
            }
            else {
                ev.target.show();
            }
        }
    </script>
@endsection
