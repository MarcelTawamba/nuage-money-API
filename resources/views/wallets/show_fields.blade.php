<!-- Wallet Info Card -->
<div class="col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-wallet mr-2"></i>Wallet Information</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="text-muted small">Currency</label>
                <h4 class="mb-0">{{ $wallet->currency->name }}</h4>
            </div>
            <div class="mb-3">
                <label class="text-muted small">Available Balance</label>
                <h3 class="text-primary mb-0">{{ $wallet->currency->name }} {{ number_format($wallet->balance, 2) }}</h3>
            </div>
            @if($wallet->user && $wallet->user->client)
            <div class="mb-3">
                <label class="text-muted small">Owner (App)</label>
                <p class="mb-0">{{ $wallet->user->client->name }}</p>
            </div>
            @if($wallet->user->client->company)
            <div class="mb-3">
                <label class="text-muted small">Company</label>
                <p class="mb-0">{{ $wallet->user->client->company->name }}</p>
            </div>
            @endif
            @endif
            <div class="mb-3">
                <label class="text-muted small">Created</label>
                <p class="mb-0">{{ $wallet->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <label class="text-muted small">Last Updated</label>
                <p class="mb-0">{{ $wallet->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Summary Card -->
<div class="col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Transaction Summary</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="text-muted small">Total Pay-ins</label>
                <h4 class="text-success mb-0">{{ $wallet->currency->name }} {{ number_format($wallet->sumPayIn(), 2) }}</h4>
            </div>
            <div class="mb-3">
                <label class="text-muted small">Total Payouts</label>
                <h4 class="text-danger mb-0">{{ $wallet->currency->name }} {{ number_format($wallet->sumPayOut(), 2) }}</h4>
            </div>
            <div class="mb-3">
                <label class="text-muted small">Total Transactions</label>
                <h4 class="mb-0">{{ $wallet->transactions->count() }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions & Payment Attempts -->
<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-history mr-2"></i>All Payment Attempts & Transactions</h5>
        </div>
        <div class="card-body">
            @if($allPaymentAttempts && $allPaymentAttempts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Reference</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allPaymentAttempts as $attempt)
                            @php
                                $statusClass = 'secondary';
                                $statusText = $attempt->status;
                                $typeClass = 'info';
                                $typeText = 'Payment Attempt';
                                
                                // Determine status styling
                                if (stripos($attempt->status, 'success') !== false || stripos($attempt->status, 'completed') !== false) {
                                    $statusClass = 'success';
                                } elseif (stripos($attempt->status, 'fail') !== false || stripos($attempt->status, 'error') !== false) {
                                    $statusClass = 'danger';
                                } elseif (stripos($attempt->status, 'pending') !== false) {
                                    $statusClass = 'warning';
                                }
                                
                                // Determine type based on requestable_type (collection vs payout)
                                if ($attempt->requestable_type === 'App\\Models\\YellowCardPayment' || 
                                    $attempt->requestable_type === 'App\\Models\\PayOutRequest') {
                                    $typeClass = 'warning';
                                    $typeText = 'Payout';
                                } elseif ($attempt->requestable_type === 'App\\Models\\YellowCardCollection' || 
                                          $attempt->requestable_type === 'App\\Models\\PayInRequest') {
                                    $typeClass = 'success';
                                    $typeText = 'Pay-in';
                                } else {
                                    // Fallback to amount-based detection
                                    if ($attempt->amount < 0) {
                                        $typeClass = 'warning';
                                        $typeText = 'Payout';
                                    } else {
                                        $typeClass = 'success';
                                        $typeText = 'Pay-in';
                                    }
                                }
                            @endphp
                            <tr>
                                <td>{{ $attempt->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $typeClass }}">{{ $typeText }}</span>
                                </td>
                                <td class="{{ $attempt->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $wallet->currency->name }} {{ number_format(abs($attempt->amount), 2) }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td><small class="text-muted">{{ $attempt->ref_id ?? 'N/A' }}</small></td>
                                <td>
                                    @if($attempt->user_ref_id)
                                        <small class="text-muted">User Ref: {{ $attempt->user_ref_id }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Showing last 20 payment attempts (including successful and failed transactions)
                    </small>
                </div>
            @elseif($wallet->transactions->count() > 0)
                <!-- Fallback to showing only successful transactions if no payment attempts data -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wallet->transactions()->latest()->take(10)->get() as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($transaction->amount > 0)
                                        <span class="badge badge-success">Pay-in</span>
                                    @else
                                        <span class="badge badge-danger">Payout</span>
                                    @endif
                                </td>
                                <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $wallet->currency->name }} {{ number_format(abs($transaction->amount), 2) }}
                                </td>
                                <td>{{ $wallet->currency->name }} {{ number_format($transaction->balance_after ?? 0, 2) }}</td>
                                <td><small class="text-muted">{{ $transaction->reference ?? 'N/A' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($wallet->transactions->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('transactions.index', ['wallet_id' => $wallet->id]) }}" class="btn btn-outline-primary">
                            View All Transactions
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No payment attempts yet</p>
                </div>
            @endif
        </div>
    </div>
</div>

