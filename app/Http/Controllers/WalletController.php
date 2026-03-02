<?php

namespace App\Http\Controllers;

use App\DataTables\WalletDataTable;
use App\Http\Requests\CreateWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\WalletType;
use App\Repositories\WalletRepository;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;

class WalletController extends AppBaseController
{
    /** @var WalletRepository $walletRepository*/
    private $walletRepository;

    public function __construct(WalletRepository $walletRepo)
    {
        $this->walletRepository = $walletRepo;
    }

    /**
     * Display a listing of the Wallet.
     */
    public function index(WalletDataTable $dataTable)
    {
        $user = Auth::user();
        
        if($user->is_admin){
            $wallets = Wallet::with('currency')->get();
        } else {
            $walletIds = [];
            foreach ($user->wallets_nuage() as $wallet) {
                $walletIds[] = $wallet->id;
            }
            $wallets = Wallet::with('currency')->whereIn('id', $walletIds)->get();
        }

        return view('wallets.index')->with('wallets', $wallets);
    }

    /**
     * Show the form for creating a new Wallet.
     */
    public function create()
    {
        $client = Auth::user()->clients;

        $clients = [];
        foreach ( $client as $can){
            $clients["$can->id"] = $can->name;
        }

        // Fetch valid fiat currencies from YellowCard - EXACTLY as done in fund-fiat-wallet
        $yellowCardService = app(\App\Services\YellowCard\YellowCardService::class);
        $currencies = [];
        
        try {
            $exchangeRates = $yellowCardService->getExchangeRates();
            if (isset($exchangeRates['rates']) && is_array($exchangeRates['rates'])) {
                foreach ($exchangeRates['rates'] as $rate) {
                     if (isset($rate['buy']) && isset($rate['sell']) && isset($rate['code'])) {
                         // Skip crypto
                         if (!isset($rate['locale']) || $rate['locale'] !== 'crypto') {
                            $currencies[$rate['code']] = $rate['code'];
                         }
                     }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('YC Rates missing in WalletController: ' . $e->getMessage());
        }
        
        // Fallback or merge with DB if API fails/empty, or just unique
        if (empty($currencies)) {
             $dbCurrencies = WalletType::all();
             foreach ($dbCurrencies as $can){
                $currencies[$can->name] = $can->name;
             }
        }

        return view('wallets.create')->with("client",$clients)->with("currencies",$currencies);
    }

    /**
     * Store a newly created Wallet in storage.
     */
    public function store(CreateWalletRequest $request)
    {
        $input = $request->all();

        $client = Client::find($input["client_id"]);
        $client_wallet = $client->wallet;
        if(!$client_wallet instanceof  ClientWallet){
            $client_wallet = new  ClientWallet();
            $client_wallet->client_id = $client->id;
            $client_wallet->save();
        }

        // Lookup currency by ID or Name (since dropdown now sends Name)
        $currency_input = $input["currency_id"];
        if (is_numeric($currency_input)) {
             $currency = WalletType::find($currency_input);
        } else {
             $currency = WalletType::where('name', $currency_input)->first();
             // If not found in DB, create it on the fly to support new YC currencies
             if (!$currency) {
                 $currency = new WalletType();
                 $currency->name = $currency_input;
                 $currency->decimals = 2; // Default
                 $currency->save();
             }
        }

        if($client_wallet->wallet( $currency->name) == null){
            $client_wallet->wallets()->create(['wallet_type_id' => $currency->id]);
        }

        Flash::success('Wallet saved successfully.');

        return redirect(route('fiat-wallets.index'));
    }

    /**
     * Display the specified Wallet.
     */
    public function show($id)
    {
        $wallet = $this->walletRepository->find($id);

        if (empty($wallet)) {
            Flash::error('Wallet not found');

            return redirect(route('fiat-wallets.index'));
        }

        $user = Auth::user();
        
        if (!$user->is_admin) {
            $hasAccess = false;
            
            if ($wallet->user_type === 'App\\Models\\User' && $wallet->user_id === $user->id) {
                $hasAccess = true;
            } elseif ($wallet->user_type === 'App\\Models\\ClientWallet') {
                if ($wallet->user && $wallet->user->client && $wallet->user->client->user_id === $user->id) {
                    $hasAccess = true;
                }
            }
            
            if (!$hasAccess) {
                Flash::error('Unauthorized access. You can only view your own wallets.');
                return redirect(route('home'));
            }
        }
        
        // Get all payment attempts (Achats) for this wallet's client and currency
        $allPaymentAttempts = collect();
        if ($wallet->user && $wallet->user->client) {
            $allPaymentAttempts = \App\Models\Achat::where('client_id', $wallet->user->client->id)
                ->where('currency', $wallet->currency->name)
                ->latest()
                ->take(20)
                ->get();
        }

        return view('wallets.show')
            ->with('wallet', $wallet)
            ->with('allPaymentAttempts', $allPaymentAttempts);
    }

    /**
     * Show the form for editing the specified Wallet.
     */
    public function edit($id)
    {
        $wallet = $this->walletRepository->find($id);

        if (empty($wallet)) {
            Flash::error('Wallet not found');

            return redirect(route('fiat-wallets.index'));
        }

        $user = Auth::user();
        
        if (!$user->is_admin) {
            Flash::error('Unauthorized access. Only admins can edit wallets.');
            return redirect(route('home'));
        }

        return view('wallets.edit')->with('wallet', $wallet);
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $wallet = $this->walletRepository->find($id);

        if (empty($wallet)) {
            Flash::error('Wallet not found');

            return redirect(route('fiat-wallets.index'));
        }

        $user = Auth::user();
        
        if (!$user->is_admin) {
            Flash::error('Unauthorized access. Only admins can delete wallets.');
            return redirect(route('home'));
        }

        $this->walletRepository->delete($id);

        Flash::success('Wallet deleted successfully.');

        return redirect(route('fiat-wallets.index'));
    }
}
