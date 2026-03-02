@extends('layouts.app')

@section('content')
    @push('third_party_stylesheets')
        @include('layouts.datatables_css')
    @endpush
    @include('flash_message')
    <div class="container-fluid px-3 px-md-4" style="background-color: white; border-radius: 1rem; padding-top: 2rem; padding-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 100%;">
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

                <div class="custom-card-compact">
                    <div class="text-center pt-3">
                        <p class="text-muted mb-1" style="font-size: 0.85rem;">{{$wallet->currency->name}}</p>
                        <h3 class="text-custom-blue mb-3">{{number_format($wallet->balance, 2)}}</h3>
                        <a href="{{ route('wallets.show', $wallet->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye mr-1"></i>View Details
                        </a>
                    </div>
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

            {{-- Fiat Wallets Section --}}
            <div class="mt-4">
                <h5 class="mb-3 text-dark-blue d-inline-block" style="background-color: rgba(255,255,255,0.8); padding: 8px 16px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <i class="fas fa-money-bill-wave mr-2 text-success"></i>
                    Fiat Wallets
                    @if(\Illuminate\Support\Facades\Auth::user()->account_type == "company")
                        <small class="text-muted">(Aggregated for all company apps)</small>
                    @endif
                </h5>
                <div class="d-flex flex-wrap">
                    @foreach($clients[0]->wallets() as $wallet)
                        <x-wallet-card 
                            :wallet="$wallet" 
                            type="fiat"
                            :show-main-badge="true"
                            :is-main-wallet="$clients[0]->main_wallet == $wallet->currency->name"
                        />
                    @endforeach
                </div>
            </div>

            {{-- Crypto Wallets Section --}}
            <div class="mt-4">
                <h5 class="mb-3 text-dark-blue d-inline-block" style="background-color: rgba(255,255,255,0.8); padding: 8px 16px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <i class="fab fa-bitcoin mr-2 text-warning"></i>
                    Crypto Wallets
                    @if(\Illuminate\Support\Facades\Auth::user()->account_type == "company")
                        <small class="text-muted">(Aggregated for all company apps & personal)</small>
                    @else
                        <small class="text-muted">(Personal wallets)</small>
                    @endif
                </h5>
                @if($cryptoWallets->isEmpty())
                    <div class="text-center py-4" style="background-color: rgba(255,255,255,0.5); border-radius: 8px; max-width: 400px;">
                        <i class="fab fa-bitcoin fa-2x text-muted mb-2"></i>
                        <h6 class="text-muted mb-2">No crypto wallets yet</h6>
                        <p class="text-muted mb-3 small">Create your first crypto wallet to start receiving digital assets</p>
                        <a href="{{ route('crypto-wallets.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle mr-1"></i>Create Crypto Wallet
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-wrap">
                        @foreach($cryptoWallets as $cryptoWallet)
                            <x-wallet-card 
                                :wallet="$cryptoWallet" 
                                type="crypto"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <br/>
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->is_admin)
            <div class="mt-4">
                <a href="{{ route('wallets.index') }}" class="btn btn-primary mr-2">
                    <i class="fas fa-money-bill-wave mr-1"></i>View All Fiat Wallets
                </a>
                <a href="{{ route('crypto-wallets.index') }}" class="btn btn-warning">
                    <i class="fab fa-bitcoin mr-1"></i>View All Crypto Wallets
                </a>
            </div>
        @else
            <div class="mt-4">
                <h5 class="text-dark-blue mb-3">
                    <i class="fas fa-bolt mr-2"></i>Quick Actions
                </h5>
                <div class="quick-actions-grid">
                    <!-- Fund Wallet -->
                    <a href="{{ route('apps.fund_fiat_wallet', [$clients[0]->id]) }}" class="action-card action-card-success">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="action-content">
                            <h6 class="action-title">Fund Wallet</h6>
                            <p class="action-subtitle">Add money to your account</p>
                        </div>
                    </a>

                    <!-- Send Fiat -->
                    <a href="{{ route('apps.withdraw', [$clients[0]->id]) }}" class="action-card action-card-primary">
                        <div class="action-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="action-content">
                            <h6 class="action-title">Send Fiat</h6>
                            <p class="action-subtitle">Transfer money</p>
                        </div>
                    </a>

                    <!-- Convert Funds -->
                    <a href="{{ route('exchange-request.create', [$clients[0]->id]) }}" class="action-card action-card-info">
                        <div class="action-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="action-content">
                            <h6 class="action-title">Convert Funds</h6>
                            <p class="action-subtitle">Exchange currencies</p>
                        </div>
                    </a>

                    <!-- Create Crypto Wallet -->
                    <a href="{{ route('crypto-wallets.create') }}" class="action-card action-card-warning">
                        <div class="action-icon">
                            <i class="fab fa-bitcoin"></i>
                        </div>
                        <div class="action-content">
                            <h6 class="action-title">New Crypto Wallet</h6>
                            <p class="action-subtitle">Create digital wallet</p>
                        </div>
                    </a>

                    <!-- View Crypto Wallets -->
                    <a href="{{ route('crypto-wallets.index') }}" class="action-card action-card-secondary">
                        <div class="action-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="action-content">
                            <h6 class="action-title">Crypto Wallets</h6>
                            <p class="action-subtitle">View all crypto assets</p>
                        </div>
                    </a>

                    <!-- Change Main Wallet -->
                    <button type="button" class="action-card action-card-dark" data-toggle="modal" data-target="#exampleModalCenters">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="action-content">
                            <h6 class="action-title">Main Wallet</h6>
                            <p class="action-subtitle">Change default currency</p>
                        </div>
                    </button>
                </div>
            </div>
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
        .top-card {
            width: 150px;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-xl);
            background-color: #fff;
            margin: 8px 10px;
            color: var(--secondary-midnight);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid var(--border-subtle);
        }
        
        .top-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        
        .top-card.bg-custom-blue {
            background: var(--primary-lilac) !important;
            color: white;
            border: none;
        }
        
        .top-card.bg-custom-blue .h3 {
            color: white;
        }

        .chart-card {
            margin: 15px 15px;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 1rem;
        }

        .custom-card-compact {
            position: relative;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            width: 200px;
            height: 145px;
            margin: 8px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .custom-card-compact .text-custom-blue {
            color: var(--primary-lilac);
        }

        .client-card {
            position: relative;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .text-custom-blue {
            color: var(--primary-lilac);
        }

        .text-dark-blue {
            color: var(--secondary-midnight);
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
            .custom-card-compact{
                margin-bottom: 10px;
                width: calc(100vw - 50px);
            }
            .client-card{

                height: 330px;
                margin-top: 15px;
            }
        }


        /* Prevent overflow */
        .card {
            overflow: hidden;
        }
        
        /* Ensure full width usage */
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Wallet Card Hover Effects */
        .wallet-card:hover, .crypto-wallet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
        }
        
        /* Prevent button overflow */
        .btn {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Collapsible Header Animation */
        .card-header[data-toggle="collapse"] {
            transition: all 0.3s ease;
        }
        .card-header[data-toggle="collapse"]:hover {
            opacity: 0.9;
        }
        
        /* Rotate chevron on collapse */
        .card-header[aria-expanded="false"] .fa-chevron-down {
            transform: rotate(-90deg);
            transition: transform 0.3s ease;
        }
        .card-header[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(0deg);
            transition: transform 0.3s ease;
        }

        /* Smooth collapse animation */
        .collapse {
            transition: height 0.35s ease;
        }

        /* Copy button feedback */
        .btn-outline-secondary:active {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
        }

        /* ========== QUICK ACTIONS GRID ========== */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            display: flex;
            align-items: center;
            padding: 1.25rem;
            border-radius: 12px;
            background: white;
            border: 2px solid transparent;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            text-decoration: none;
        }

        .action-card:hover::before {
            transform: scaleY(1);
        }

        .action-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .action-content {
            flex: 1;
            text-align: left;
        }

        .action-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #2B5587;
        }

        .action-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 0;
        }

        /* Color Variants */
        .action-card-success {
            border-color: #d4edda;
            color: #28a745;
        }
        .action-card-success:hover {
            border-color: #28a745;
            background: linear-gradient(135deg, #ffffff 0%, #f1f9f4 100%);
        }
        .action-card-success .action-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .action-card-primary {
            border-color: #d1e7fd;
            color: #007bff;
        }
        .action-card-primary:hover {
            border-color: #007bff;
            background: linear-gradient(135deg, #ffffff 0%, #e7f3ff 100%);
        }
        .action-card-primary .action-icon {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .action-card-info {
            border-color: #d1ecf1;
            color: #17a2b8;
        }
        .action-card-info:hover {
            border-color: #17a2b8;
            background: linear-gradient(135deg, #ffffff 0%, #e8f7f9 100%);
        }
        .action-card-info .action-icon {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .action-card-warning {
            border-color: #fff3cd;
            color: #ffc107;
        }
        .action-card-warning:hover {
            border-color: #ffc107;
            background: linear-gradient(135deg, #ffffff 0%, #fffaeb 100%);
        }
        .action-card-warning .action-icon {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: white;
        }

        .action-card-secondary {
            border-color: #e2e3e5;
            color: #6c757d;
        }
        .action-card-secondary:hover {
            border-color: #6c757d;
            background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%);
        }
        .action-card-secondary .action-icon {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
        }

        .action-card-dark {
            border-color: #f8d7da;
            color: #dc3545;
        }
        .action-card-dark:hover {
            border-color: #dc3545;
            background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
        }
        .action-card-dark .action-icon {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .quick-actions-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .action-card {
                padding: 1rem;
            }
            
            .action-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
            
            .action-title {
                font-size: 0.95rem;
            }
            
            .action-subtitle {
                font-size: 0.8rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>

    <script>
        // Copy to clipboard function
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                // Change icon temporarily
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check';
                button.classList.add('btn-success');
                button.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    icon.className = originalClass;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 2000);
            }, function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy address');
            });
        }

        // Update chevron rotation on collapse toggle
        $(document).ready(function() {
            $('.collapse').on('show.bs.collapse', function() {
                $(this).prev().find('.fa-chevron-down').css('transform', 'rotate(0deg)');
            });
            $('.collapse').on('hide.bs.collapse', function() {
                $(this).prev().find('.fa-chevron-down').css('transform', 'rotate(-90deg)');
            });
        });
    </script>

    <!-- Resources -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script src="{{ asset('js/home-charts.js') }}"></script>

    <script>
        var transData = JSON.parse('{!! $trans !!}');
        var transStatData = JSON.parse('{!! $trans_stat !!}');
        initializeCharts(transData, transStatData);
    </script>
@endsection
