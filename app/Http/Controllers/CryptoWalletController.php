<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCryptoWallet;
use App\Models\Company;
use App\Models\CryptoAsset;
use App\Models\User;
use App\Services\BlockRadarService;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CryptoWalletController extends AppBaseController
{
    private BlockRadarService $blockRadarService;

    public function __construct(BlockRadarService $blockRadarService)
    {
        $this->blockRadarService = $blockRadarService;
    }

    /**
     * Display a listing of crypto wallets
     */
    public function index()
    {
        $user = auth()->user();
        
        $query = ClientCryptoWallet::with(['client', 'cryptoAsset']);
        
        if (!$user->is_admin) {
            $query->whereHas('client', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $wallets = $query->latest()->paginate(20);

        return view('crypto_wallets.index', compact('wallets'));
    }

    /**
     * Show the form for creating a new crypto wallet
     */
    public function create()
    {
        $user = auth()->user();
        
        // Get the user's company
        $company = Company::where('user_id', $user->id)->first();
        
        // Build clients array with sections
        $clients = [];
        
        // Section 1: Current User (for individual wallets)
        $clients['My Account'] = [
            'user_' . $user->id => $user->name . ' (Personal)'
        ];
        
        // Section 2: Company's Clients/Apps (if user has a company)
        if ($company) {
            $companyClients = Client::where('company_id', $company->id)
                ->where('personal_access_client', 0)
                ->where('password_client', 0)
                ->get()
                ->pluck('name', 'id')
                ->toArray();
            
            if (!empty($companyClients)) {
                $clients['Company Apps'] = $companyClients;
            }
        }
        
        // Get all active crypto assets
        $cryptoAssets = CryptoAsset::active()->get()->mapWithKeys(function ($asset) {
            return [$asset->id => $asset->display_name];
        });

        return view('crypto_wallets.create', compact('clients', 'cryptoAssets'));
    }

    /**
     * Store a newly created crypto wallet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'crypto_asset_id' => 'required|exists:crypto_assets,id',
        ]);

        $clientId = $validated['client_id'];
        
        // Check if this is a personal wallet (prefixed with 'user_')
        if (Str::startsWith($clientId, 'user_')) {
            // For personal wallets, we need to create a client if doesn't exist
            $userId = (int) str_replace('user_', '', $clientId);
            $user = User::findOrFail($userId);
            
            // Check if user already has a personal client for crypto
            $client = Client::where('user_id', $userId)
                ->where('name', 'LIKE', '%Personal Crypto%')
                ->first();
            
            if (!$client) {
                // Create a personal client for this user
                $client = Client::create([
                    'id' => Str::uuid(),
                    'user_id' => $userId,
                    'name' => $user->name . ' - Personal Crypto Wallet',
                    'secret' => Str::random(40),
                    'redirect' => '',
                    'personal_access_client' => 0,
                    'password_client' => 0,
                    'revoked' => 0,
                    'company_id' => 0,
                ]);
            }
        } else {
            // Regular company client
            $client = Client::findOrFail($clientId);
        }
        
        $cryptoAsset = CryptoAsset::findOrFail($validated['crypto_asset_id']);

        // Check if wallet already exists
        $existingWallet = ClientCryptoWallet::where('client_id', $client->id)
            ->where('crypto_asset_id', $cryptoAsset->id)
            ->first();

        if ($existingWallet) {
            Flash::warning("Wallet already exists for {$client->name} on {$cryptoAsset->display_name}");
            return redirect(route('crypto-wallets.index'));
        }

        // Create address on BlockRadar
        $masterWalletId = $cryptoAsset->blockradar_wallet_id;
        $metadata = [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'asset_symbol' => $cryptoAsset->asset_symbol,
            'blockchain' => $cryptoAsset->blockchain_name,
        ];

        $addressData = $this->blockRadarService->createAddress(
            $masterWalletId,
            $metadata,
            "{$client->name} - {$cryptoAsset->asset_symbol}"
        );

        if (!$addressData) {
            Flash::error('Failed to create wallet address on BlockRadar. Please try again.');
            return redirect()->back()->withInput();
        }

        // Store in database
        $wallet = ClientCryptoWallet::create([
            'client_id' => $client->id,
            'crypto_asset_id' => $cryptoAsset->id,
            'blockradar_address_id' => $addressData['id'],
            'deposit_address' => $addressData['address'],
            'balance' => 0,
            'is_active' => true,
        ]);

        Flash::success("Crypto wallet created successfully! Deposit address: {$wallet->deposit_address}");
        return redirect(route('crypto-wallets.show', $wallet->id));
    }

    /**
     * Display the specified crypto wallet
     */
    public function show($id)
    {
        $wallet = ClientCryptoWallet::with(['client', 'cryptoAsset'])->findOrFail($id);

        $user = auth()->user();
        
        if (!$user->is_admin) {
            if ($wallet->client->user_id !== $user->id) {
                Flash::error('Unauthorized access. You can only view your own wallets.');
                return redirect(route('home'));
            }
        }

        return view('crypto_wallets.show', compact('wallet'));
    }

    /**
     * Fund a crypto wallet (Admin only)
     */
    public function fund(Request $request, $id)
    {
        $user = auth()->user();
        
        if (!$user->is_admin) {
            Flash::error('Unauthorized. Only administrators can fund wallets.');
            return redirect()->back();
        }

        $wallet = ClientCryptoWallet::with(['client', 'cryptoAsset'])->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.00000001',
            'reference' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Convert amount to smallest unit (consider decimals)
        $rawAmount = $validated['amount'] * pow(10, $wallet->cryptoAsset->decimals);

        // Update wallet balance
        $wallet->balance += $rawAmount;
        $wallet->last_deposit_at = now();
        $wallet->save();

        // Create transaction record (you may want to create a CryptoTransaction model)
        // TODO: Log this transaction in a transactions table

        Flash::success("Wallet funded successfully! Added {$validated['amount']} {$wallet->cryptoAsset->asset_symbol}");
        return redirect()->route('crypto-wallets.show', $wallet->id);
    }

    /**
     * Send funds from crypto wallet (Admin only)
     */
    public function send(Request $request, $id)
    {
        $user = auth()->user();
        
        if (!$user->is_admin) {
            Flash::error('Unauthorized. Only administrators can send funds.');
            return redirect()->back();
        }

        $wallet = ClientCryptoWallet::with(['client', 'cryptoAsset'])->findOrFail($id);

        $validated = $request->validate([
            'address' => 'required|string',
            'amount' => 'required|numeric|min:0.00000001',
            'reference' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Convert amount to smallest unit
        $rawAmount = $validated['amount'] * pow(10, $wallet->cryptoAsset->decimals);

        // Check sufficient balance
        if ($wallet->balance < $rawAmount) {
            Flash::error('Insufficient balance. Available: ' . $wallet->formatted_balance . ' ' . $wallet->cryptoAsset->asset_symbol);
            return redirect()->back();
        }

        // Here you would integrate with BlockRadar or YellowCard to actually send the crypto
        // For now, we'll just update the balance
        
        try {
            // TODO: Call BlockRadar/YellowCard API to send crypto
            // $this->blockRadarService->sendCrypto($wallet, $validated['address'], $rawAmount);

            // Update wallet balance
            $wallet->balance -= $rawAmount;
            $wallet->last_withdrawal_at = now();
            $wallet->save();

            // TODO: Log this transaction in a transactions table

            Flash::success("Funds sent successfully! Sent {$validated['amount']} {$wallet->cryptoAsset->asset_symbol} to {$validated['address']}");
            return redirect()->route('crypto-wallets.show', $wallet->id);
        } catch (\Exception $e) {
            Flash::error('Failed to send funds: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
