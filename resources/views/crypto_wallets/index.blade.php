@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Crypto Wallets</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('crypto-wallets.create') }}">
                        <i class="fas fa-plus"></i> Create New Crypto Wallet
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card">
            <div class="card-body">
                @if($wallets->isEmpty())
                    <div class="alert alert-info">
                        <i class="icon fas fa-info"></i>
                        No crypto wallets created yet. Click "Create New Crypto Wallet" to get started.
                    </div>
                @else
                    <div class="d-flex flex-wrap">
                        @foreach($wallets as $wallet)
                            <x-wallet-card :wallet="$wallet" type="crypto" />
                        @endforeach
                    </div>

                    @if($wallets->hasPages())
                        <div class="card-footer">
                            {{ $wallets->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <x-wallet-card-styles />
@endsection
