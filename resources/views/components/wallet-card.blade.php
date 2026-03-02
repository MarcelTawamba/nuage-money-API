@props([
    'wallet',
    'type' => 'fiat', // 'fiat' or 'crypto'
    'showMainBadge' => false,
    'isMainWallet' => false
])

<div class="custom-card-compact {{ $isMainWallet ? 'border border-primary' : '' }}" 
     style="{{ $type === 'crypto' ? 'background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);' : '' }}">
    <div class="text-center pt-3">
        @if($showMainBadge && $isMainWallet)
            <span class="badge badge-primary mb-2" style="font-size: 0.7rem;">Main Wallet</span>
        @endif
        
        @if($type === 'crypto' && isset($wallet->cryptoAsset))
            <span class="badge badge-{{ $wallet->cryptoAsset->network === 'mainnet' ? 'success' : 'warning' }} mb-1" style="font-size: 0.65rem;">
                {{ $wallet->cryptoAsset->network }}
            </span>
            <p class="text-muted mb-1" style="font-size: 0.85rem;">{{ $wallet->cryptoAsset->asset_symbol }}</p>
            <h3 class="text-dark mb-3" style="font-size: 1.3rem;">{{ $wallet->formatted_balance }}</h3>
        @else
            <p class="text-muted mb-1" style="font-size: 0.85rem;">{{ $wallet->currency->name }}</p>
            <h3 class="text-custom-blue mb-3">{{ number_format($wallet->balance, 2) }}</h3>
        @endif
        
        <a href="{{ $type === 'crypto' ? route('crypto-wallets.show', $wallet->id) : route('fiat-wallets.show', $wallet->id) }}" 
           class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye mr-1"></i>View Details
        </a>
    </div>
</div>
