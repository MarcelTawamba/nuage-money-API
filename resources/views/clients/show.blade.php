@extends('layouts.app')

@section('content')
    @if(\Illuminate\Support\Facades\Auth::user()->is_admin)


    @endif

    <div  class="container-fluid p-3" style="background-color: white">


                    @if(\Illuminate\Support\Facades\Auth::user()->is_admin)
                        <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>
                                Client Details
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <a class="btn btn-default float-right"
                               href="{{ route('apps.index') }}">
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </section>
                        <div class="">
                            <div class="card-body">
                                    <div class="row">

                                        @include('clients.show_fields')
                                    </div>
                            </div>
                        </div>
                    @else
                        <div class="d-flex flex-wrap">
                            <div class="mb-4 mb-md-0">
                                <div class="client-card  m-md-4 client-card-color-0">
                                    <img class="" src="{{url("images/pattern0.png")}}">
                                    <div class="m-3 client-card-content">
                                        <div class="">
                                            <div class="d-flex mr-5 mb-4" style="align-items: center;">
                                                <p class="h1 mb-0 ml-2">{{ $client->name}}</p>
                                            </div>

                                            <div class="d-flex mr-5 mb-3" style="align-items: center">
                                                <p class="h6 mb-0">Company&nbsp;:</p>
                                                <p class="h6 mb-0 ml-2">{{ $client->company->name }}</p>
                                            </div>


                                            <div class="d-flex mr-5 mb-3" style="align-items: center;">
                                                <p class="h6 mb-0">Redirect&nbsp;:</p>
                                                <p class="h6 mb-0 ml-2">{{ $client->redirect }}</p>
                                            </div>

                                            <div class="d-flex mr-5 mb-5" style="align-items: center;">
                                                <p class="h6 mb-0">Secret&nbsp;:</p>

                                                @if(Session::has('secret'))
                                                    <p class="h6 mb-0 ml-2 secret">{{ Session::get('secret')}}</p>
                                                @else

                                                    <p class="h6 mb-0 ml-2 secret">******************************</p>
                                                @endif
                                            </div>
                                            <div class="d-flex">
                                                @if(Session::has('secret'))
                                                   <a href="{{route('apps.show',$client->id)}}"  class="btn btn-secondary mr-2 show-button" >Hide Secret</a>
                                                    {{ session()->forget('secret') }}
                                                @else
                                                    <!-- Button trigger modal -->
                                                    <button type="button" class="btn btn-secondary mr-2 show-button" data-toggle="modal" data-target="#exampleModalCenter">
                                                        Show Secret
                                                    </button>

                                                    <!-- Modal -->
                                                    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <form method="POST" action="{{route('apps.show_secret',$client->id)}}" >
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

                                                <a href="{{ route('apps.edit', [$client->id]) }}"
                                                   class='btn bg-warning  mr-2'>
                                                    <i class="fa fa-cog mr-1"></i>Setting
                                                </a>
                                                {!! Form::open(['route' => ['apps.generate',$client->id], 'method' => 'post']) !!}

                                                <button class="btn btn-primary mr-2" onclick= "return confirm('Are you sure you want to regenerate the secret?')">Regenerate Secret</button

                                                {!! Form::close() !!}

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="d-flex flex-wrap my-md-4 ">
                                @foreach($client->wallets() as $wallet)

                                    <div class="custom-card">
                                        <div class="img-div"><img src="{{url('images/money.png')}}"></div>
                                        <p class="mt-3 ml-3 mb-1 text-dark-blue">
                                            Available Balance
                                        </p>
                                        <p class="ml-3 h4 mb-3 text-custom-blue">{{$wallet->currency->name}} {{$wallet->balance}} </p>
                                        <p class="ml-3 mb-2 text-dark-blue">Total pay-ins: <span class="text-custom-blue">{{$wallet->currency->name}} {{$wallet->sumPayIn()}}</span></p>
                                        <p class="ml-3 text-dark-blue">Total payouts: <span class="text-custom-blue">{{$wallet->currency->name}} {{$wallet->sumPayOut()}}</span></p>

                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="table-responsive mb-5 p-1 p-md-3">
                            <table class="table table-striped table-bordered dataTable " id="transactions-table">
                                <thead class="">
                                <tr>
                                    <th>Reference</th>
                                    <th>Wallet</th>
                                    <th>Amount</th>
                                    <th>Balance Before</th>
                                    <th>Balance After</th>
                                    <th colspan="3">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($client->transactions(10) as $transaction)
                                    <tr>
                                        <td>{{ $transaction->reference }}</td>
                                        <td>   @if($transaction->wallet->user->name !=null)
                                                {{ $transaction->wallet->user->name}}
                                            @else
                                                {{ $transaction->wallet->user->client_id}}
                                            @endif
                                            ({{ $transaction->wallet->currency->name }})</td>
                                        <td>{{ $transaction->amount }}</td>
                                        <td>{{ $transaction->balance_before }}</td>
                                        <td>{{ $transaction->balance_after }}</td>
                                        <td  style="width: 120px">
                                            <div class='btn-group'>
                                                <a href="{{ route('transactions.show', [$transaction->id]) }}"
                                                   class='btn btn-default btn-xs'>
                                                    <i class="far fa-eye"></i>
                                                </a>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex flex-row-reverse">
                                <a href="{{ route('transactions.index') . "?service=".$client->id }}" class="btn ml-2 bg-custom-blue ">View all transaction  <i class="ml-1 fas fa-arrow-alt-circle-right"></i></a>

                            </div>
                        </div>

                    @endif

    </div>

    <style>


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
        }
    </style>

{{--    <script>--}}
{{--        let secret = document.querySelector('.secret')--}}

{{--        let button = document.querySelector('.show-button')--}}

{{--        button.addEventListener('click',(e)=>{--}}
{{--            if( button.innerHTML === "Show Secret"){--}}
{{--                button.innerHTML = "Hide Secret"--}}
{{--                secret.innerHTML = "{{$client->secret}}"--}}
{{--            }else{--}}
{{--                button.innerHTML = "Show Secret"--}}
{{--                secret.innerHTML = "******************************"--}}
{{--            }--}}
{{--        })--}}
{{--    </script>--}}
@endsection
