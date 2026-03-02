@extends('layouts.app')

@section('content')
    <div class="container-fluid px-3 px-md-4" style="background-color: white; border-radius: 1rem; padding-top: 2rem; padding-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 100%;">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div class="mb-2 mb-md-0">
                <h2 class="mb-1">
                    <i class="fas fa-wallet text-primary mr-2"></i>Wallet Details
                </h2>
                <p class="text-muted mb-0">{{ $wallet->currency->name }} Wallet</p>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                @if(!Auth::user()->is_admin && $wallet->user && $wallet->user->client)
                    <a class="btn btn-success mr-2 mb-2" href="{{ route('apps.fund_fiat_wallet', [$wallet->user->client->id, 'currency' => $wallet->currency->name]) }}">
                        <i class="fas fa-plus-circle mr-1"></i>Fund Wallet
                    </a>
                    <a class="btn btn-primary mr-2 mb-2" href="{{ route('apps.withdraw', [$wallet->user->client->id, 'currency' => $wallet->currency->name]) }}">
                        <i class="fas fa-paper-plane mr-1"></i>Send
                    </a>
                @endif
                @if(\Illuminate\Support\Facades\Auth::user()->is_admin)
                    <a class="btn btn-outline-primary mr-2 mb-2" href="{{ route('fiat-wallets.index') }}">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Wallets
                    </a>
                    <a class="btn btn-outline-secondary mb-2" href="{{ route('home') }}">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                @else
                    <a class="btn btn-outline-primary mb-2" href="{{ route('fiat-wallets.index') }}">
                        <i class="fas fa-arrow-left mr-1"></i>Back
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            @include('wallets.show_fields')
        </div>
    </div>
@endsection
