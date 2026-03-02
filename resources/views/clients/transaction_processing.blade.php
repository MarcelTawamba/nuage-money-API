@extends('layouts.app')

@section('content')
<div class="content px-0 px-md-3" style="background-color: white; min-height: 80vh;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 text-center">
                    <h1>
                        <i class="fas fa-sync-alt fa-spin text-primary mr-2"></i>Processing Transaction
                    </h1>
                    <p class="text-muted">Please wait while we confirm your transaction...</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-body text-center py-5">
                        <!-- Spinning Circle -->
                        <div class="spinner-container mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 5rem; height: 5rem; border-width: 0.4rem;">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>

                        <!-- Processing Message -->
                        <h3 class="mb-3" id="status-message">Processing your transaction...</h3>
                        
                        <!-- Transaction Details -->
                        <div class="transaction-details mt-4 mb-4">
                            <p class="mb-2">
                                <strong>Amount:</strong> 
                                <span class="text-primary">{{ number_format($achat->amount, 2) }} {{ $achat->currency }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Transaction ID:</strong> 
                                <span class="text-muted font-monospace small">{{ $achat->ref_id }}</span>
                            </p>
                            <p class="mb-0">
                                <strong>Status:</strong> 
                                <span class="badge badge-warning" id="current-status">{{ ucfirst($achat->status) }}</span>
                            </p>
                        </div>

                        <!-- Progress Info -->
                        <div class="alert alert-info mt-3" id="progress-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span id="progress-text">Checking transaction status...</span>
                        </div>

                        <!-- Timeout Warning (hidden initially) -->
                        <div class="alert alert-warning mt-3 d-none" id="timeout-warning">
                            <i class="fas fa-clock mr-2"></i>
                            This is taking longer than expected. You'll be redirected to your wallet shortly.
                        </div>
                    </div>
                </div>

                <!-- Manual Redirect Option -->
                <div class="text-center mt-3">
                    <a href="{{ $walletUrl }}" class="btn btn-outline-secondary">
                        <i class="fas fa-wallet mr-2"></i>Go to Wallet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
    <div id="toast-container"></div>
</div>

@endsection

@push('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Transaction processing page loaded');
    
    const achatId = {{ $achat->id }};
    const walletUrl = "{{ $walletUrl }}";
    const statusCheckUrl = "{{ route('apps.check_transaction_status', $achat->id) }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    console.log('Configuration:', {
        achatId: achatId,
        walletUrl: walletUrl,
        statusCheckUrl: statusCheckUrl
    });
    
    const POLL_INTERVAL = 3000; // 3 seconds
    const MAX_DURATION = 45000; // 45 seconds timeout
    const startTime = Date.now();
    
    let pollCount = 0;
    let pollTimer = null;
    
    // Helper function to get element
    function $(selector) {
        return document.querySelector(selector);
    }
    
    // Toast notification function (simplified)
    function showToast(message, type = 'info') {
        console.log(`[${type.toUpperCase()}] ${message}`);
        // Toasts are optional - just log for now
    }
    
    // Update progress text
    function updateProgress() {
        pollCount++;
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const progressText = $('#progress-text');
        if (progressText) {
            progressText.textContent = `Checking transaction status... (${elapsed}s elapsed, attempt ${pollCount})`;
        }
    }
    
    // Check transaction status
    function checkStatus() {
        const elapsed = Date.now() - startTime;
        
        // Check if we've exceeded max duration
        if (elapsed > MAX_DURATION) {
            handleTimeout();
            return;
        }
        
        updateProgress();
        
        // Show timeout warning at 30 seconds
        const timeoutWarning = $('#timeout-warning');
        if (elapsed > 30000 && timeoutWarning && timeoutWarning.classList.contains('d-none')) {
            timeoutWarning.classList.remove('d-none');
        }
        
        console.log(`Checking status (attempt ${pollCount}, elapsed: ${Math.floor(elapsed/1000)}s)...`);
        
        fetch(statusCheckUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(response => {
            console.log('Status check response:', response);
            
            if (response.success) {
                const currentStatus = $('#current-status');
                if (currentStatus) {
                    currentStatus.textContent = response.status.toUpperCase();
                }
                
                if (response.final) {
                    // Transaction reached final state
                    clearInterval(pollTimer);
                    
                    // Normalize status to uppercase for comparison
                    const statusUpper = response.status.toUpperCase();
                    console.log('Final status detected:', statusUpper);
                    
                    if (statusUpper === 'SUCCESSFUL') {
                        handleSuccess(response);
                    } else if (statusUpper === 'FAILED') {
                        handleFailure(response);
                    }
                } else {
                    console.log('Status not final yet:', response.status);
                }
            } else {
                console.error('Status check failed:', response.message);
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
            // Continue polling even on error
        });
    }
    
    // Handle successful transaction
    function handleSuccess(response) {
        const statusMessage = $('#status-message');
        const currentStatus = $('#current-status');
        const progressInfo = $('#progress-info');
        
        if (statusMessage) {
            statusMessage.innerHTML = '<i class="fas fa-check-circle text-success mr-2"></i>Transaction Successful!';
        }
        if (currentStatus) {
            currentStatus.classList.remove('badge-warning');
            currentStatus.classList.add('badge-success');
            currentStatus.textContent = 'SUCCESSFUL';
        }
        if (progressInfo) {
            progressInfo.classList.remove('alert-info');
            progressInfo.classList.add('alert-success');
            progressInfo.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Your wallet has been credited successfully!';
        }
        
        showToast(`Transaction completed successfully! Amount: ${response.amount} ${response.currency}`, 'success');
        
        // Redirect after 2 seconds
        setTimeout(function() {
            window.location.href = walletUrl;
        }, 2000);
    }
    
    // Handle failed transaction
    function handleFailure(response) {
        const statusMessage = $('#status-message');
        const currentStatus = $('#current-status');
        const progressInfo = $('#progress-info');
        
        if (statusMessage) {
            statusMessage.innerHTML = '<i class="fas fa-times-circle text-danger mr-2"></i>Transaction Failed';
        }
        if (currentStatus) {
            currentStatus.classList.remove('badge-warning');
            currentStatus.classList.add('badge-danger');
            currentStatus.textContent = 'FAILED';
        }
        if (progressInfo) {
            progressInfo.classList.remove('alert-info');
            progressInfo.classList.add('alert-danger');
            progressInfo.innerHTML = '<i class="fas fa-times-circle mr-2"></i>The transaction could not be completed. Please try again or contact support.';
        }
        
        showToast('Transaction failed. Please check your wallet or try again.', 'error');
        
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = walletUrl;
        }, 3000);
    }
    
    // Handle timeout
    function handleTimeout() {
        clearInterval(pollTimer);
        
        const statusMessage = $('#status-message');
        const progressInfo = $('#progress-info');
        const timeoutWarning = $('#timeout-warning');
        
        if (statusMessage) {
            statusMessage.innerHTML = '<i class="fas fa-clock text-warning mr-2"></i>Still Processing...';
        }
        if (progressInfo) {
            progressInfo.classList.remove('alert-info');
            progressInfo.classList.add('alert-warning');
            progressInfo.innerHTML = '<i class="fas fa-hourglass-half mr-2"></i>The transaction is taking longer than usual. Check your wallet for updates.';
        }
        if (timeoutWarning) {
            timeoutWarning.classList.remove('d-none');
        }
        
        showToast('Transaction is still processing. You can check your wallet for the latest status.', 'warning');
        
        // Dispatch background job to continue monitoring
        fetch("{{ route('apps.dispatch_background_job', $achat->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(response => {
            console.log('Background monitoring activated:', response);
        })
        .catch(error => {
            console.error('Failed to activate background monitoring:', error);
        });
        
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = walletUrl;
        }, 3000);
    }
    
    // Start polling immediately
    checkStatus();
    pollTimer = setInterval(checkStatus, POLL_INTERVAL);
});
</script>
@endpush
