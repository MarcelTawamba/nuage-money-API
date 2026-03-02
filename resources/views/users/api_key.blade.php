@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">
                        API Key Management
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fas fa-exclamation-triangle"></i> API Key Required</h4>
                <p>{{ session('warning') }}</p>
                <p class="mb-0">
                    <strong>Don't worry!</strong> You're in the right place. Simply click the "Generate API Key" button below to get started.
                </p>
            </div>
        @endif

        @if(session('new_api_key'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> API Key Generated!</h5>
                <p><strong>Copy this key now. It will not be shown again:</strong></p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="newApiKey" value="{{ session('new_api_key') }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                @if(session('redirect_url'))
                    <hr>
                    <p class="mb-2">
                        <strong>✓ You're all set!</strong> You can now access all features.
                    </p>
                    <a href="{{ session('redirect_url') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Continue to Dashboard
                    </a>
                @endif
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your API Key</h3>
            </div>
            <div class="card-body">
                <p>Use this API key to authenticate your requests to the Nuage Money API. This key has full access to all API endpoints.</p>
                
                @if($apiKeys->isEmpty())
                    <div class="alert alert-info">
                        <i class="icon fas fa-info"></i>
                        You don't have an API key yet. Generate one below to get started.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Key Preview</th>
                                    <th>Environment</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Last Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apiKeys as $key)
                                    <tr>
                                        <td>{{ $key->name }}</td>
                                        <td>
                                            <code>nuage_{{ $key->environment }}_{{ $key->key_prefix }}_****</code>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $key->environment === 'live' ? 'success' : 'info' }}">
                                                {{ strtoupper($key->environment) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($key->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $key->created_at->format('M d, Y') }}</td>
                                        <td>{{ $key->last_used_at ? $key->last_used_at->format('M d, Y H:i') : 'Never' }}</td>
                                        <td>
                                            <form action="{{ route('users.api_key.revoke', $key->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to revoke this API key? This action cannot be undone and any applications using this key will stop working.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="icon fas fa-exclamation-triangle"></i>
                        <strong>Security Note:</strong> Keep your API key secure. Do not share it publicly or commit it to version control.
                    </div>
                @endif
            </div>
            <div class="card-footer">
                @if($apiKeys->isEmpty() || !$apiKeys->where('is_active', true)->count())
                    <form action="{{ route('users.api_key.generate') }}" method="POST" onsubmit="return confirm('Are you sure you want to generate a new API key?')">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Generate API Key
                        </button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="icon fas fa-info-circle"></i>
                        You already have an active API key. If you need a new one, please contact support or revoke your current key first.
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">API Documentation</h3>
            </div>
            <div class="card-body">
                <h5>How to use your API Key</h5>
                <p>Include your API key in the <code>X-API-Key</code> header of your HTTP requests:</p>
                <pre class="bg-light p-3 rounded"><code>curl -H "X-API-Key: your_api_key_here" \
  {{ config('app.url') }}/api/v1/your-endpoint</code></pre>
                
                <h5 class="mt-4">Available Scopes</h5>
                <p>Your API key has full access (<code>*</code> scope) to all endpoints including:</p>
                <ul>
                    <li><strong>Currencies:</strong> Read currency information</li>
                    <li><strong>Countries:</strong> Read available countries</li>
                    <li><strong>Fees:</strong> Read fee information</li>
                    <li><strong>Payments:</strong> Read and create payments/transactions</li>
                    <li><strong>Wallets:</strong> Read wallet balances and perform wallet operations</li>
                    <li><strong>Crypto Wallets:</strong> Create and manage crypto wallets</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function copyApiKey() {
            var copyText = document.getElementById("newApiKey");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand("copy");
                alert("API Key copied to clipboard!");
            } catch(err) {
                alert("Failed to copy. Please copy manually.");
            }
        }
    </script>
@endsection
