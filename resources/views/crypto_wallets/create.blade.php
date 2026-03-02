@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Create Crypto Wallet</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card">
            {!! Form::open(['route' => 'crypto-wallets.store']) !!}

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    <strong>How it works:</strong> Select a client and crypto asset. A unique deposit address will be generated for the client on the selected blockchain.
                </div>

                <div class="row">
                    <!-- Client/App Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('client_id', 'Client / App:') !!}<span class="text-danger">*</span>
                        {!! Form::select('client_id', $clients, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Client/App']) !!}
                    </div>

                    <!-- Crypto Asset Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('crypto_asset_id', 'Blockchain & Asset:') !!}<span class="text-danger">*</span>
                        {!! Form::select('crypto_asset_id', $cryptoAssets, null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Blockchain & Asset']) !!}
                        <small class="form-text text-muted">Format: SYMBOL - Blockchain (network)</small>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Note:</strong> 
                    <ul class="mb-0">
                        <li>Each client can only have ONE wallet per blockchain/asset combination</li>
                        <li>The wallet will be created under Nuage's master wallet on BlockRadar</li>
                        <li>A unique deposit address will be generated and cannot be changed</li>
                    </ul>
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Create Crypto Wallet', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('crypto-wallets.index') }}" class="btn btn-default">Cancel</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
