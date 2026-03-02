<?php

namespace App\Http\Controllers;

use App\Classes\ExchangeHelper;
use App\Classes\GeneralPaymentHelper;
use App\Classes\StartButtonAfricaPaymentHelper;
use App\Classes\ToupesuGeneralPaymentHelpers;
use App\Classes\ToupesuPhoneNumber;
use App\Enums\MethodType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PayType;
use App\Events\PayInSuccessEvent;
use App\Http\Requests\CreateClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Achat;
use App\Models\AdminDepositeRequest;
use App\Models\ClientWallet;
use App\Models\Company;
use App\Models\CountryAvaillable;
use App\Models\CustomFee;
use App\Models\Operator;
use App\Models\StartButton\Bank;
use App\Models\ToupesuPaymentRequest;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Models\YellowCardCollection;
use App\Jobs\CheckYellowCardTransactionJob;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Passport\ClientRepository;
use App\Models\Client;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Laravel\Passport\Http\Rules\RedirectRule;
use function Termwind\render;

class ClientController extends AppBaseController
{
    /** @var ClientRepository $clientRepository*/
    private $clientRepository;

    /**
     * The redirect validation rule.
     *
     * @var \Laravel\Passport\Http\Rules\RedirectRule
     */
    protected $redirectRule;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    public function __construct(ClientRepository $clientRepo, ValidationFactory $validation, RedirectRule $redirectRule)
    {
        $this->clientRepository = $clientRepo;
        $this->validation = $validation;
        $this->redirectRule = $redirectRule;
    }

    /**
     * Display a listing of the Client.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->is_admin) {
            $clients = Client::paginate(10);
        } else {
            $clients = Client::where("user_id", $user->id)->paginate(10);
        }

        return view('clients.index')
            ->with('clients', $clients);
    }

    /**
     * Show the form for creating a new Client.
     */
    public function create()
    {
        $user = Auth::user();

        if($user->is_admin){
            $company = Company::all();
        }else{
            $company = Company::where('user_id',$user->id)->get();
        }

        $companies = [];
        foreach ($company as $com){
            $companies[$com->id]=$com->name;
        }


        return view('clients.create')->with('company', $companies);
    }

    /**
     * Store a newly created Client in storage.
     */
    public function store(CreateClientRequest $request)
    {
        $input = $request->all();

        $this->validation->make($request->all(), [
            'name' => Rule::unique('oauth_clients')->where(fn ($query) => $query->where('user_id', Auth::user()->id)),
            'redirect'     => ['required', $this->redirectRule],
            'company_id'   => "required|string|exists:companies,id",
            'confidential' => 'boolean',
        ])->validate();


        $client = $this->clientRepository->create(
            Auth::user()->id, $request->name, $request->redirect,
            null, false, false, (bool) $request->input('confidential', true)
        );
        $client->company_id = $request->company_id;
        $client->save();

        Flash::success('App saved successfully.');

        return redirect(route('home'));
    }

    /**
     * Store a newly created Client in storage.
     */
    public function regenerate($id,Request $request)
    {

        $user = Auth::user();
        $client = Client::where("id",$id)->where("user_id",$user->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $client->secret = Str::random(40);
        $client->save();


        Flash::success('secret regenerated successfully.');

        return redirect(route('home'));
    }

    /**
     * Store a newly created Client in storage.
     */
    public function changeWallet(Request $request)
    {

        $user = Auth::user();

        $company = Company::whereUserId($user->id)->first();
        
        if (!$company) {
            Flash::error('Company not found');
            return redirect(route('home'));
        }
        
        $client = Client::whereUserId($user->id)->where("company_id", $company->id)->first();

        if (!$client) {
            Flash::error('Client not found');
            return redirect(route('home'));
        }

        $currency = WalletType::find($request->input("main_wallet"));

        if (!$currency instanceof WalletType) {
            Flash::error('Currency not found');
            return redirect(route('home'));
        }

        $curr = WalletType::whereName($client->main_wallet)->first();

        $wallet = Wallet::whereUserType(ClientWallet::class)
            ->where("user_id", $client->wallet->id)
            ->where("wallet_type_id", $curr->id)->first();

        if (!$wallet instanceof Wallet) {
            $wallet = new Wallet();
            $wallet->user_id = $client->wallet->id;
            $wallet->user_type = ClientWallet::class;
            $wallet->wallet_type_id = $curr->id;
            $wallet->raw_balance = 0;

            $wallet->save();
        }
        
        if ($wallet->balance > 0) {
            Flash::error($curr->name . ' balance must be 0 for you to change the main wallet');
            return redirect(route('home'));
        }

        $client->main_wallet = $currency->name;
        $client->save();

        Flash::success('Main wallet changed successfully');

        return redirect(route('home'));

    }



    /**
     * Store a newly created Client in storage.
     */
    public function showSecret($id,Request $request)
    {

        $user = Auth::user();
        $client = Client::where("id",$id)->where("user_id",$user->id)->first();

        $input = $request->all();

        $password = "";
        if(isset($input['pass'])){
            $password = $input['pass'];
        }



        if( Hash::check($password,$user->password)){
            session(['secret' => $client->secret]);

        }else{
            Flash::error('Password not correct');
        }
        return redirect(route('home'));


    }
    /**
     * Display the specified Client.
     */
    public function show($id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        return view('clients.show')->with('client', $client);
    }

    /**
     * Show the form for editing the specified Client.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $client = Client::where("id",$id)->where("user_id",$user->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        if($user->is_admin){
            $company = Company::all();
        }else{
            $company = Company::where('user_id',$user->id)->get();
        }

        $companies = [];
        foreach ($company as $com){
            $companies[$com->id]=$com->name;
        }



        return view('clients.edit')->with('client', $client)->with('company',$companies);
    }

    /**
     * Update the specified Client in storage.
     */
    public function update($id, UpdateClientRequest $request)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $data = $request->all();


        $this->validation->make($request->all(), [
            'name' => Rule::unique('App\Models\Client')->ignore($client->id),
            'redirect' => ['required', $this->redirectRule],
        ])->validate();

        $client = $this->clientRepository->update(
            $client, $data['name'], $data['redirect']
        );

        Flash::success('App updated successfully.');

        return redirect(route('home'));
    }

    /**
     * Remove the specified Client from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        $this->clientRepository->delete($client);
        $client->delete();
        Flash::success('App deleted successfully.');

        return redirect(route('home'));
    }

    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function fundWalletView(Request $request, $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        // Fetch ONLY fiat currencies from YellowCard exchange rates
        $yellowCardService = app(\App\Services\YellowCard\YellowCardService::class);
        
        try {
            $exchangeRatesResponse = $yellowCardService->getExchangeRates();
            $channelsResponse = $yellowCardService->getChannels();
            $networksResponse = $yellowCardService->getNetworks();
        } catch (\Exception $e) {
            Log::error('YellowCard API error: ' . $e->getMessage());
            Flash::error('Failed to load YellowCard data: ' . $e->getMessage());
            return redirect(route('home'));
        }
        
        $currency = [];
        $defaultCurrency = $request->query('currency'); // Get currency from query parameter
        
        if (isset($exchangeRatesResponse['rates']) && is_array($exchangeRatesResponse['rates'])) {
            foreach ($exchangeRatesResponse['rates'] as $rate) {
                // Only include FIAT currencies that have both buy and sell rates
                if (isset($rate['buy']) && isset($rate['sell']) && isset($rate['code'])) {
                    // Skip crypto currencies - only add fiat
                    if (!isset($rate['locale']) || $rate['locale'] !== 'crypto') {
                        $currency[$rate['code']] = $rate['code'];
                    }
                }
            }
        }
        
        // Fallback to WalletType if YellowCard API fails
        if (empty($currency)) {
            Log::warning('YellowCard API failed, using wallet-based currency list');
            
            // Get all wallet types that the client has wallets for
            $clientWallet = $client->wallet;
            if ($clientWallet) {
                $wallets = Wallet::where('user_type', ClientWallet::class)
                    ->where('user_id', $clientWallet->id)
                    ->with('currency')
                    ->get();
                
                foreach ($wallets as $wallet) {
                    if ($wallet->currency) {
                        $currency[$wallet->currency->name] = $wallet->currency->name;
                    }
                }
            }
            
            // If still empty, fall back to main wallet
            if (empty($currency)) {
                $currencies = WalletType::whereName($client->main_wallet)->get();
                foreach ($currencies as $cur) {
                    $currency[$cur->name] = $cur->name;
                }
            }
            
            // If defaultCurrency not provided and currency array is not empty,
            // set first available currency as default
            if (empty($defaultCurrency) && !empty($currency)) {
                $defaultCurrency = array_key_first($currency);
            }
        }

        $methods = Operator::where("type",PayType::PAY_IN)->get();

        $countries = CountryAvaillable::all();
        $country = [];

        foreach ($countries as $count){
            $country[$count->id] = $count->name;
        }

        // Extract channels and networks data
        $channels = $channelsResponse['channels'] ?? [];
        $networks = $networksResponse['networks'] ?? [];

        return view('clients.fund_wallet')
            ->with('client', $client)
            ->with('currency', $currency)
            ->with('defaultCurrency', $defaultCurrency)
            ->with('method', $methods)
            ->with('channels', json_encode($channels))
            ->with('networks', json_encode($networks));
    }

    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function fundWallet(Request $request, $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');
            return redirect(route('home'));
        }

        // Validate split name fields if recipient data is provided
        if ($request->has('recipient')) {
            $validationRules = [
                'recipient.firstName' => 'sometimes|required|string|max:255',
                'recipient.lastName' => 'sometimes|required|string|max:255',
            ];
            
            // Nigeria-specific validation
            $recipientCountry = strtoupper($request->input('recipient.country', ''));
            if ($recipientCountry === 'NG' && $request->input('customerType', 'retail') === 'retail') {
                $validationRules['recipient.idType'] = 'required|string|in:NIN,BVN';
                $validationRules['recipient.additionalIdType'] = 'required|string|in:NIN,BVN';
                $validationRules['recipient.additionalIdNumber'] = 'required|string';
            }
            
            $request->validate($validationRules);
            
            // Additional Nigeria validation: ensure idType and additionalIdType are different
            if ($recipientCountry === 'NG' && $request->input('customerType', 'retail') === 'retail') {
                $idType = $request->input('recipient.idType');
                $additionalIdType = $request->input('recipient.additionalIdType');
                
                if ($idType === $additionalIdType) {
                    Flash::error('For Nigeria: ID Type and Additional ID Type must be different (one NIN, one BVN)');
                    return redirect()->back()->withInput();
                }
                
                // Ensure we have both NIN and BVN
                $hasNIN = $idType === 'NIN' || $additionalIdType === 'NIN';
                $hasBVN = $idType === 'BVN' || $additionalIdType === 'BVN';
                
                if (!$hasNIN || !$hasBVN) {
                    Flash::error('For Nigeria: You must provide both NIN and BVN (one as ID Type, one as Additional ID Type)');
                    return redirect()->back()->withInput();
                }
            }
        }

        // Check if YellowCard data is provided
        if ($request->has('channelId')) {
            // Use YellowCard service for collection
            try {
                $yellowCardService = app(\App\Services\YellowCard\YellowCardService::class);
                $user = Auth::user();
                
                $customerUID = (string)($user->uuid ?? ('customer_' . $user->id));
                // Generate or retrieve customer UID for YellowCard

                // Prepare payload for YellowCard collection
                $payload = [
                    'channelId' => $request->input('channelId'),
                    'sequenceId' => 'coll_' . uniqid() . '_' . $client->id,
                    'customerUID' => $customerUID,
                    'customerType' => $request->input('customerType', 'retail'),
                    'forceAccept' => $request->boolean('forceAccept', false),
                ];

                // Add amount (either USD or local currency)
                if ($request->filled('amount')) {
                    $payload['amount'] = $request->input('amount');
                }
                if ($request->filled('localAmount')) {
                    $payload['localAmount'] = $request->input('localAmount');
                }

                // Add recipient information - ensure phone is included
                $recipient = $request->input('recipient');
                
                // EXPLICIT phone field extraction
                if (!isset($recipient['phone']) || empty($recipient['phone'])) {
                    Log::warning('Phone field missing from recipient, attempting fallback extraction');
                    
                    // Try to extract it differently
                    if ($request->has('recipient.phone')) {
                        $recipient['phone'] = $request->input('recipient.phone');
                        Log::info('Manually added phone from recipient.phone');
                    }
                }
                
                $payload['recipient'] = $recipient;

                // Add source account information
                $payload['source'] = $request->input('source');

                // Add redirect URL if forceAccept is true
                if ($payload['forceAccept'] && $request->filled('redirectUrl')) {
                    $payload['redirectUrl'] = $request->input('redirectUrl');
                } elseif ($payload['forceAccept']) {
                    // Try to find the specific wallet for this currency to provide a better redirect
                    $currencyName = $request->input('currency', $client->main_wallet);
                    $wallet = Wallet::where('user_id', $client->wallet->id)
                        ->where('user_type', ClientWallet::class)
                        ->whereHas('currency', function($q) use ($currencyName) {
                            $q->where('name', $currencyName);
                        })->first();
                    $payload['redirectUrl'] = $wallet ? route('fiat-wallets.show', $wallet->id) : route('fiat-wallets.index');
                }

                Log::info('YellowCard collection initiated', [
                    'sequence_id' => $payload['sequenceId'],
                    'currency' => $payload['channelId'] ?? null,
                    'has_recipient' => isset($payload['recipient']),
                    'has_source' => isset($payload['source'])
                ]);

                // Submit collection request to YellowCard
                $response = $yellowCardService->submitCollection($payload);

                if (isset($response['success']) && $response['success']) {
                    Flash::success('Collection request submitted successfully via YellowCard');
                    
                    // Create local transaction records to track this collection
                    $ycCollection = new YellowCardCollection();
                    $ycCollection->sequence_id = $payload['sequenceId'];
                    $ycCollection->yellowcard_id = $response['data']['id'] ?? null;
                    $ycCollection->status = $response['data']['status'] ?? 'pending';
                    $ycCollection->amount = $payload['amount'] ?? $payload['localAmount'] ?? 0;
                    $ycCollection->currency = $request->input('currency');
                    $ycCollection->customer_uid = $payload['customerUID'];
                    $ycCollection->channel_id = $payload['channelId'];
                    $ycCollection->payment_url = $response['data']['paymentUrl'] ?? null;
                    $ycCollection->response_data = $response['data'];
                    $ycCollection->save();

                    $new_achat = new Achat();
                    $new_achat->client_id = $client->id;
                    $new_achat->amount = $ycCollection->amount;
                    $new_achat->currency = $ycCollection->currency;
                    $new_achat->country = $request->input('country');
                    $new_achat->ref_id = $ycCollection->sequence_id;
                    $new_achat->user_ref_id = $ycCollection->sequence_id;
                    $new_achat->status = PaymentStatus::getStatus(strtoupper($ycCollection->status));
                    $new_achat->requestable()->associate($ycCollection);
                    $new_achat->save();

                    // If there's a payment URL/redirect, redirect to it (external payment flow)
                    // For external flows, dispatch background job immediately as user won't be on our page
                    if (isset($response['data']['paymentUrl'])) {
                        CheckYellowCardTransactionJob::dispatch($new_achat)->delay(now()->addSeconds(30));
                        return redirect($response['data']['paymentUrl']);
                    }
                    
                    // For internal flow: Redirect to processing page with achat ID for status polling
                    // Background job will be dispatched by frontend if polling times out (see transaction_processing.blade.php)
                    return redirect()->route('apps.transaction_processing', ['achatId' => $new_achat->id]);
                } else {
                    $errorMessage = $response['message'] ?? 'Failed to submit collection request';
                    return back()->withErrors(['error' => $errorMessage])->withInput();
                }

            } catch (\Exception $e) {
                Log::error('YellowCard collection error: ' . $e->getMessage());
                return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
            }
        }

        // Legacy payment methods (Toupesu, StartButton, etc.)
        $input = $request->all();
        $method = Operator::whereId($input["method"])->where('currency_id',$client->mainCurrency()->id)->first();
        
        if($method instanceof Operator){
            if( strtolower($method->method_class) == strtolower(PaymentMethod::TOUPESU_MOBILE)){
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "user-". GeneralPaymentHelper::UUID(),
                    "msidn"=> $input["msidn"]
                ];
                $result = ToupesuGeneralPaymentHelpers::initPayment($inputs);
                $result = json_decode($result->content());
                if( ! $result->success){
                    return redirect(route('apps.fund_fiat_wallet', $id))
                        ->withErrors(["errors"=>"An error has occur during the request"])
                        ->withInput();
                }

                Flash::success('Mobile request send, please validate on your phone');

            }elseif(strtolower($method->method_class) == strtolower(PaymentMethod::START_BUTTON_BANK)){
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "user-". GeneralPaymentHelper::UUID(),
                    "email"=> $input["email"]
                ];
                $result = StartButtonAfricaPaymentHelper::initPayment($inputs);
                $result = json_decode($result->content());
                if($result->success){
                    return redirect($result->payment_link);
                }else{
                    return redirect(route('apps.fund_fiat_wallet', $id))
                        ->withErrors(["errors"=>"An error has occur during the request"])
                        ->withInput();
                }
            }else{
                return redirect(route('apps.fund_fiat_wallet', $id))
                    ->withErrors(["errors"=>"Payment method not found"])
                    ->withInput();
            }
        }else{
            return redirect(route('apps.fund_fiat_wallet', $id))
                ->withErrors(["errors"=>"Payment method not found"])
                ->withInput();
        }

        return redirect(route('home'));
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function withdrawView(Request $request, $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('home'));
        }

        // Fetch YellowCard data for withdraw
        $yellowCardService = app(\App\Services\YellowCard\YellowCardService::class);
        
        try {
            $exchangeRatesResponse = $yellowCardService->getExchangeRates();
            $channelsResponse = $yellowCardService->getChannels();
            $networksResponse = $yellowCardService->getNetworks();
        } catch (\Exception $e) {
            Log::error('YellowCard API error: ' . $e->getMessage());
            Flash::error('Failed to load YellowCard data: ' . $e->getMessage());
            return redirect(route('home'));
        }
        
        $currency = [];
        $defaultCurrency = $request->query('currency'); // Get currency from query parameter
        
        if (isset($exchangeRatesResponse['rates']) && is_array($exchangeRatesResponse['rates'])) {
            foreach ($exchangeRatesResponse['rates'] as $rate) {
                // Only include FIAT currencies that have both buy and sell rates
                if (isset($rate['buy']) && isset($rate['sell']) && isset($rate['code'])) {
                    // Skip crypto currencies - only add fiat
                    if (!isset($rate['locale']) || $rate['locale'] !== 'crypto') {
                        $currency[$rate['code']] = $rate['code'];
                    }
                }
            }
        }
        
        // Fallback to wallet-based currencies if YellowCard API fails
        if (empty($currency)) {
            Log::warning('YellowCard API failed, using wallet-based currency list');
            
            $clientWallet = $client->wallet;
            if ($clientWallet) {
                $wallets = Wallet::where('user_type', ClientWallet::class)
                    ->where('user_id', $clientWallet->id)
                    ->with('currency')
                    ->get();
                
                foreach ($wallets as $wallet) {
                    if ($wallet->currency) {
                        $currency[$wallet->currency->name] = $wallet->currency->name;
                    }
                }
            }
            
            // Set first available currency as default if not provided
            if (empty($defaultCurrency) && !empty($currency)) {
                $defaultCurrency = array_key_first($currency);
            }
        }

        // Extract channels and networks data
        $channels = $channelsResponse['channels'] ?? [];
        $networks = $networksResponse['networks'] ?? [];

        return view('clients.withdraw_wallet')
            ->with('client', $client)
            ->with('currency', $currency)
            ->with('defaultCurrency', $defaultCurrency)
            ->with('channels', $channels)
            ->with('networks', $networks);

    }


    /**
     * Process withdrawal via YellowCard or legacy methods
     *
     * @throws \Exception
     * @throws GuzzleException
     */
    public function withdraw(Request $request, $id)
    {
        $client = Client::whereId($id)->where("user_id",Auth::user()->id)->first();

        if (empty($client)) {
            Flash::error('App not found');
            return redirect(route('home'));
        }

        // Check if YellowCard data is provided
        if ($request->has('destination')) {
            // Use YellowCard service for payment (payout)
            try {
                $yellowCardService = app(\App\Services\YellowCard\YellowCardService::class);
                $user = Auth::user();
                
                // Validate that we have required destination fields
                // Note: amount OR localAmount is required, validated below
                $request->validate([
                    'destination.accountNumber' => 'required|string',
                    'destination.accountType' => 'required|string|in:bank,momo',
                    'currency' => 'required|string',
                    'reason' => 'required|string',
                ]);
                
                // Validate that either amount or localAmount is provided
                if (!$request->filled('amount') && !$request->filled('localAmount')) {
                    Flash::error('Either amount (USD) or local currency amount must be provided');
                    return redirect()->back()->withInput();
                }
                
                $customerUID = (string)(method_exists($user, 'getCustomerUid') 
                    ? $user->getCustomerUid()
                    : ($user->uuid ?? ('customer_' . $user->id)));

                // Prepare payload for YellowCard payment
                $payload = [
                    'sequenceId' => 'pymt_' . uniqid() . '_' . $client->id,
                    'customerUID' => $customerUID,
                    'customerType' => $request->input('customerType', 'retail'),
                    'forceAccept' => $request->boolean('forceAccept', true),
                    'reason' => $request->input('reason', 'Withdrawal request'),
                    'currency' => $request->input('currency'),
                ];

                // Add amount (either USD or local currency)
                if ($request->filled('amount')) {
                    $payload['amount'] = $request->input('amount');
                }
                if ($request->filled('localAmount')) {
                    $payload['localAmount'] = $request->input('localAmount');
                }

                // Add sender information (the business/user making the payout)
                $payload['sender'] = $request->input('sender');

                // Add destination account information
                $payload['destination'] = $request->input('destination');

                Log::info('YellowCard payout initiated', [
                    'sequence_id' => $payload['sequenceId'],
                    'currency' => $request->input('currency'),
                    'amount' => $payload['localAmount'] ?? $payload['amount'] ?? null,
                    'client_id' => $client->id
                ]);

                // Submit payment request to YellowCard
                $response = $yellowCardService->submitPayment($payload);

                if (isset($response['success']) && $response['success']) {
                    Flash::success('Payment request submitted successfully via YellowCard');
                    
                    // Create local transaction records to track this payment
                    $ycPayment = new \App\Models\YellowCardPayment();
                    $ycPayment->sequence_id = $payload['sequenceId'];
                    $ycPayment->yellowcard_id = $response['data']['id'] ?? null;
                    $ycPayment->status = $response['data']['status'] ?? 'pending';
                    $ycPayment->amount = $payload['localAmount'];
                    $ycPayment->currency = $request->input('currency');
                    $ycPayment->customer_uid = $payload['customerUID'];
                    $ycPayment->response_data = $response['data'];
                    $ycPayment->save();

                    $new_achat = new Achat();
                    $new_achat->client_id = $client->id;
                    $new_achat->amount = $ycPayment->amount;
                    $new_achat->currency = $ycPayment->currency;
                    $new_achat->country = $request->input('sender.country', 'NG');
                    $new_achat->ref_id = $ycPayment->sequence_id;
                    $new_achat->user_ref_id = $ycPayment->sequence_id;
                    $new_achat->status = PaymentStatus::getStatus(strtoupper($ycPayment->status));
                    $new_achat->requestable()->associate($ycPayment);
                    $new_achat->save();

                    // Redirect to payout processing page for status tracking
                    return redirect()->route('apps.payout_processing', ['achatId' => $new_achat->id]);
                } else {
                    $errorMessage = $response['message'] ?? 'Failed to submit payment request';
                    return back()->withErrors(['error' => $errorMessage])->withInput();
                }

            } catch (\Exception $e) {
                Log::error('YellowCard payment error: ' . $e->getMessage());
                return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
            }
        }

        // Legacy payment methods (Toupesu, StartButton, etc.)
        $input = $request->all();

        $method = Operator::whereId($input["method"])->where('currency_id',"!=",$client->mainCurrency()->id)->first();


        $currency= $method->currency;

        if( !($client->wallet instanceof ClientWallet)){

            return  back()
                ->withErrors(["message"=>"Solde insuffisant"])
                ->withInput();
        }

        $wallet = Wallet::where("user_type",ClientWallet::class)->where('user_id', $client->wallet->id)->where("wallet_type_id",$currency->id)->first();

        if( !($wallet instanceof Wallet)){

            return  back()
                ->withErrors(["message"=>"Solde insuffisant"])
                ->withInput();
        }

        $fees = $method;
        $custom_fee = CustomFee::where("company_id",$client->company_id)->where("method_id",$method->id)->first();

        if($custom_fee instanceof  CustomFee){
            $fees = $custom_fee;
        }

        $total_fee_amount = $fees->fee_type == "percentage" ?  -1 * $input["amount"]* ( $fees->fees / 100 ) : $fees->fees;

        if( $wallet->balance < ($input["amount"] +  $total_fee_amount) ){
            $input["amount_to"] = ($input["amount"] +  $total_fee_amount);
            $result =  ExchangeHelper::paymentDiffCurrency($input,$client,$currency);

            if(!$result["success"]){
                return  back()
                    ->withErrors(["message"=>$result["message"]])
                    ->withInput();
            }
        }





        if($method instanceof Operator){
            if( strtolower($method->method_class) == strtolower(PaymentMethod::TOUPESU_MOBILE)){
                $phone = new  ToupesuPhoneNumber($input["msidn"]);

                if( !$phone->IsValidNumber()){
                    return back()
                        ->withErrors(["message"=> "Provide a valid mobile number"])
                        ->withInput();
                }
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "User-". GeneralPaymentHelper::UUID(),
                    "msidn"=> $input["msidn"]
                ];
                $result = ToupesuGeneralPaymentHelpers::initPayout($inputs);
                $result = json_decode($result->content());
                info("result",["data"=>$result]);
                if( ! $result->success){
                    return  back()
                        ->withErrors(["message"=>$result->message])
                        ->withInput();
                }


            }elseif(strtolower($method->method_class) == strtolower(PaymentMethod::START_BUTTON_BANK)){
                $inputs = [
                    "service"=> $client->id,
                    "country"=> $method->country->code,
                    "currency"=>$method->currency->name,
                    "amount"=> $input["amount"],
                    "ref_id"=> "User-". GeneralPaymentHelper::UUID(),
                    "bank_code"=> $input["bank_code"],
                    "account_name"=> $input["account_name"],
                    "account_number"=> $input["account_number"]
                ];
                $result = StartButtonAfricaPaymentHelper::initPayout($inputs);
                $result = json_decode($result->content());

                if( !$result->success){

                    return  back()
                        ->withErrors(["message"=>$result->message])
                        ->withInput();
                }
            }else{
                return  back()
                    ->withErrors(["message"=>"Payment method not found"])
                    ->withInput();
            }

        }else{

            return back()->withErrors(["message"=>"Payment method not found"])
                ->withInput();
        }

        Flash::success('Votre demande a été initialisée');

        return redirect(route('home'));
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function adminFundWalletView( $id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('apps.index'));
        }

        // Fetch ONLY fiat currencies from YellowCard exchange rates
        $yellowCardService = app(\App\Services\YellowCard\YellowCardService::class);
        $exchangeRatesResponse = $yellowCardService->getExchangeRates();
        
        $currency = [];
        
        if (isset($exchangeRatesResponse['rates']) && is_array($exchangeRatesResponse['rates'])) {
            foreach ($exchangeRatesResponse['rates'] as $rate) {
                // Only include FIAT currencies that have both buy and sell rates
                if (isset($rate['buy']) && isset($rate['sell']) && isset($rate['code'])) {
                    // Skip crypto currencies - only add fiat
                    if (!isset($rate['locale']) || $rate['locale'] !== 'crypto') {
                        $currency[$rate['code']] = $rate['code'];
                    }
                }
            }
        }
        
        // Fallback to WalletType if YellowCard API fails
        if (empty($currency)) {
            $currencies = WalletType::all();
            foreach ($currencies as $cur) {
                $currency[$cur->name] = $cur->name;
            }
        }

        $countries = CountryAvaillable::all();
        $country = [];

        foreach ($countries as $count){
            $country[$count->code] = $count->name;
        }

        return view('clients.fund_wallet_admin')
            ->with('client', $client)
            ->with('currency', $currency)
            ->with('country', $country);
    }


    /**
     * Remove the specified Wallet from storage.
     *
     * @throws \Exception
     */
    public function adminFundWallet(Request $request, $id)
    {
        $client = Client::find($id);

        if (empty($client)) {
            Flash::error('App not found');

            return redirect(route('apps.index'));
        }
        $input = $request->all();

        $new_achat = new  Achat();
        $new_achat->client_id = $client->id;
        $new_achat->amount = $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["reference"];
        $new_achat->ref_id = "Admin-".GeneralPaymentHelper::UUID();
        $new_admin_deposit_request = new AdminDepositeRequest();
        $new_admin_deposit_request->description = $input['description'];
        $new_admin_deposit_request->status = PaymentStatus::SUCCESSFUL;

        $new_admin_deposit_request->save();

        $new_achat->requestable()->associate( $new_admin_deposit_request);
        $new_achat->status= PaymentStatus::SUCCESSFUL;
        $new_achat->save();
        PayInSuccessEvent::dispatch($new_achat);
        Flash::success('Deposit has been made');

        return redirect(route('home'));
    }

    /**
     * Show transaction processing page
     */
    public function showTransactionProcessing(Request $request, $achatId)
    {
        $achat = Achat::find($achatId);
        
        if (empty($achat)) {
            Flash::error('Transaction not found');
            return redirect(route('home'));
        }
        
        // Verify user owns this transaction
        $client = Client::find($achat->client_id);
        if (!$client || $client->user_id !== Auth::user()->id) {
            Flash::error('Unauthorized access');
            return redirect(route('home'));
        }
        
        // Get the wallet redirect URL for after processing
        $wallet = Wallet::where('user_id', $client->wallet->id)
            ->where('user_type', ClientWallet::class)
            ->whereHas('currency', function($q) use ($achat) {
                $q->where('name', $achat->currency);
            })->first();
        $walletUrl = $wallet ? route('fiat-wallets.show', $wallet->id) : route('fiat-wallets.index');
        
        // If transaction is already in final state, redirect immediately
        if (in_array($achat->status, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED])) {
            if ($achat->status === PaymentStatus::SUCCESSFUL) {
                Flash::success('Transaction completed successfully! Amount: ' . $achat->amount . ' ' . $achat->currency);
            } else {
                Flash::error('Transaction failed. Please try again or contact support.');
            }
            return redirect($walletUrl);
        }
        
        return view('clients.transaction_processing')
            ->with('achat', $achat)
            ->with('walletUrl', $walletUrl)
            ->with('client', $client);
    }

    /**
     * Show payout processing page (same as transaction processing but for payouts)
     */
    public function showPayoutProcessing(Request $request, $achatId)
    {
        // Reuse the same logic as transaction processing
        return $this->showTransactionProcessing($request, $achatId);
    }

    /**
     * Check transaction status via AJAX
     * Returns JSON with current status
     */
    public function checkTransactionStatus(Request $request, $achatId)
    {
        $achat = Achat::find($achatId);
        
        if (empty($achat)) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
        
        // Verify user owns this transaction
        $client = Client::find($achat->client_id);
        if (!$client || $client->user_id !== Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Check if already in final state
        if (in_array($achat->status, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED])) {
            Log::info('Transaction already in final state', [
                'achat_id' => $achat->id,
                'status' => $achat->status
            ]);
            
            return response()->json([
                'success' => true,
                'status' => $achat->status,
                'final' => true,
                'amount' => $achat->amount,
                'currency' => $achat->currency
            ]);
        }
        
        // Query YellowCard API for latest status
        try {
            Log::info('Checking transaction status via API', [
                'achat_id' => $achat->id,
                'current_status' => $achat->status,
                'requestable_type' => $achat->requestable_type
            ]);
            
            if ($achat->requestable_type == YellowCardCollection::class) {
                $result = \App\Classes\YellowCardPaymentHelper::checkRequestCollection($achat);
            } else {
                $result = \App\Classes\YellowCardPaymentHelper::checkRequestPayout($achat);
            }
            
            // Reload achat to get updated status
            $achat->refresh();
            
            $isFinal = in_array($achat->status, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED]);
            
            Log::info('Transaction status check result', [
                'achat_id' => $achat->id,
                'new_status' => $achat->status,
                'is_final' => $isFinal,
                'check_result' => $result
            ]);
            
            return response()->json([
                'success' => true,
                'status' => $achat->status,
                'final' => $isFinal,
                'amount' => $achat->amount,
                'currency' => $achat->currency
            ]);
            
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'status' => $achat->status,
                'final' => false,
                'message' => 'Status check failed',
                'amount' => $achat->amount,
                'currency' => $achat->currency
            ]);
        }
    }

    /**
     * Dispatch background job for long-running transactions
     * Called by frontend when polling times out
     */
    public function dispatchBackgroundJob(Request $request, $achatId)
    {
        $achat = Achat::find($achatId);
        
        if (empty($achat)) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
        
        // Verify user owns this transaction
        $client = Client::find($achat->client_id);
        if (!$client || $client->user_id !== Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Only dispatch if not already in final state
        if (!in_array($achat->status, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED])) {
            // Dispatch with immediate execution (no delay)
            CheckYellowCardTransactionJob::dispatch($achat);
            
            Log::info('Background job dispatched for long-running transaction', [
                'achat_id' => $achat->id,
                'ref_id' => $achat->ref_id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Background monitoring activated'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Transaction already completed'
        ]);
    }

}
