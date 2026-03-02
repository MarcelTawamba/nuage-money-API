@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Crypto Wallet Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('crypto-wallets.index') }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    @if($wallet->cryptoAsset->logo_url)
                        <img src="{{ $wallet->cryptoAsset->logo_url }}" alt="{{ $wallet->cryptoAsset->asset_symbol }}" style="width: 30px; height: 30px; margin-right: 10px;">
                    @endif
                    {{ $wallet->cryptoAsset->asset_symbol }} Wallet
                </h3>
                @if($wallet->is_active)
                    <span class="badge badge-success float-right">Active</span>
                @else
                    <span class="badge badge-danger float-right">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Client Info -->
                    <div class="col-sm-12 mb-4">
                        <h5>Client Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6">
                                {!! Form::label('client_name', 'Client Name:') !!}
                                <p><strong>{{ $wallet->client->name }}</strong></p>
                            </div>
                            <div class="col-sm-6">
                                {!! Form::label('client_id', 'Client ID:') !!}
                                <p><code>{{ $wallet->client_id }}</code></p>
                            </div>
                        </div>
                    </div>

                    <!-- Blockchain Info -->
                    <div class="col-sm-12 mb-4">
                        <h5>Blockchain & Asset Details</h5>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                {!! Form::label('blockchain', 'Blockchain:') !!}
                                <p>
                                    <span class="badge badge-info badge-lg">{{ $wallet->cryptoAsset->blockchain_name }}</span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                {!! Form::label('asset', 'Asset:') !!}
                                <p>
                                    <strong>{{ $wallet->cryptoAsset->asset_name }} ({{ $wallet->cryptoAsset->asset_symbol }})</strong>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                {!! Form::label('network', 'Network:') !!}
                                <p>
                                    <span class="badge badge-{{ $wallet->cryptoAsset->network === 'mainnet' ? 'success' : 'warning' }}">
                                        {{ strtoupper($wallet->cryptoAsset->network) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit Address -->
                    <div class="col-sm-12 mb-4">
                        <h5>Deposit Address</h5>
                        <hr>
                        {!! Form::label('deposit_address', 'Address:') !!}
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="depositAddress" value="{{ $wallet->deposit_address }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyAddress()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">
                            Send {{ $wallet->cryptoAsset->asset_symbol }} on {{ $wallet->cryptoAsset->blockchain_name }} {{ $wallet->cryptoAsset->network }} to this address.
                        </small>
                    </div>

                    <!-- Balance -->
                    <div class="col-sm-12 mb-4">
                        <h5>Balance</h5>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6">
                                {!! Form::label('balance', 'Current Balance:') !!}
                                <p class="h3">
                                    {{ $wallet->formatted_balance }} 
                                    <small class="text-muted">{{ $wallet->cryptoAsset->asset_symbol }}</small>
                                </p>
                            </div>
                            <div class="col-sm-6">
                                {!! Form::label('decimals', 'Decimals:') !!}
                                <p>{{ $wallet->cryptoAsset->decimals }}</p>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#fundWalletModal">
                                    <i class="fas fa-plus-circle"></i> Fund Wallet
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#sendFundsModal">
                                    <i class="fas fa-paper-plane"></i> Send Funds
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Activity -->
                    <div class="col-sm-12">
                        <h5>Activity</h5>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                {!! Form::label('created_at', 'Created:') !!}
                                <p>{{ $wallet->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="col-sm-4">
                                {!! Form::label('last_deposit_at', 'Last Deposit:') !!}
                                <p>{{ $wallet->last_deposit_at ? $wallet->last_deposit_at->format('M d, Y H:i') : 'Never' }}</p>
                            </div>
                            <div class="col-sm-4">
                                {!! Form::label('last_withdrawal_at', 'Last Withdrawal:') !!}
                                <p>{{ $wallet->last_withdrawal_at ? $wallet->last_withdrawal_at->format('M d, Y H:i') : 'Never' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- BlockRadar Info -->
                    <div class="col-sm-12 mt-4">
                        <div class="alert alert-secondary">
                            <strong>BlockRadar Integration:</strong><br>
                            <small>
                                <strong>Address ID:</strong> <code>{{ $wallet->blockradar_address_id }}</code><br>
                                <strong>Master Wallet ID:</strong> <code>{{ $wallet->cryptoAsset->blockradar_wallet_id }}</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fund Wallet Modal -->
    <div class="modal fade" id="fundWalletModal" tabindex="-1" role="dialog" aria-labelledby="fundWalletModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('crypto-wallets.fund', $wallet->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="fundWalletModalLabel">Fund Wallet</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fund_amount">Amount ({{ $wallet->cryptoAsset->asset_symbol }})</label>
                            <input type="number" class="form-control" id="fund_amount" name="amount" step="0.00000001" required>
                            <small class="form-text text-muted">Enter the amount to add to this wallet</small>
                        </div>
                        <div class="form-group">
                            <label for="fund_reference">Reference</label>
                            <input type="text" class="form-control" id="fund_reference" name="reference" required>
                            <small class="form-text text-muted">Transaction reference for tracking</small>
                        </div>
                        <div class="form-group">
                            <label for="fund_description">Description</label>
                            <textarea class="form-control" id="fund_description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Fund Wallet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Send Funds Modal -->
    <div class="modal fade" id="sendFundsModal" tabindex="-1" role="dialog" aria-labelledby="sendFundsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('crypto-wallets.send', $wallet->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendFundsModalLabel">Send Funds</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Available Balance:</strong> {{ $wallet->formatted_balance }} {{ $wallet->cryptoAsset->asset_symbol }}
                        </div>
                        <div class="form-group">
                            <label for="send_address">Recipient Address</label>
                            <input type="text" class="form-control" id="send_address" name="address" required>
                            <small class="form-text text-muted">{{ $wallet->cryptoAsset->blockchain_name }} address on {{ $wallet->cryptoAsset->network }}</small>
                        </div>
                        <div class="form-group">
                            <label for="send_amount">Amount ({{ $wallet->cryptoAsset->asset_symbol }})</label>
                            <input type="number" class="form-control" id="send_amount" name="amount" step="0.00000001" required max="{{ $wallet->balance }}">
                            <small class="form-text text-muted">Maximum: {{ $wallet->formatted_balance }} {{ $wallet->cryptoAsset->asset_symbol }}</small>
                        </div>
                        <div class="form-group">
                            <label for="send_reference">Reference</label>
                            <input type="text" class="form-control" id="send_reference" name="reference" required>
                        </div>
                        <div class="form-group">
                            <label for="send_description">Description</label>
                            <textarea class="form-control" id="send_description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Funds</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function copyAddress() {
            var copyText = document.getElementById("depositAddress");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert('Deposit address copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
@endsection
