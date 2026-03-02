<div class="card-body px-0 px-md-4">
    <div class="d-flex flex-wrap">
        @forelse($wallets as $wallet)
            <x-wallet-card :wallet="$wallet" type="fiat" />
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>No wallets found.
                </div>
            </div>
        @endforelse
    </div>
</div>
