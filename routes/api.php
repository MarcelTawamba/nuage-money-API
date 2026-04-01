<?php

use App\Events\PayOutFailureEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('startbutton-callback', [\App\Http\Controllers\API\StartButton\WebHookController::class, 'handleWebhook']);

// Authentication routes (for getting Passport tokens)
Route::prefix('auth')->group(function () {
    Route::post('login', [App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\API\AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [App\Http\Controllers\API\AuthController::class, 'me'])->middleware('auth:api');
});

// API Key Management routes (protected by Passport bearer token)
Route::middleware('auth:api')->prefix('api-keys')->group(function () {
    Route::get('/', [App\Http\Controllers\API\ApiKeyController::class, 'index']);
    Route::post('/', [App\Http\Controllers\API\ApiKeyController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\API\ApiKeyController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\API\ApiKeyController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\API\ApiKeyController::class, 'destroy']);
    Route::post('/{id}/regenerate', [App\Http\Controllers\API\ApiKeyController::class, 'regenerate']);
});

Route::group(['prefix' => 'extension'], function () {
    Route::post('activate', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'activate']);
    Route::post('deactivate', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'deactivate']);
    Route::post('webhook', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'webhook']);
    Route::post('rotate', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'rotate']);
});


// Authentication routes for API key management
Route::post("login", [App\Http\Controllers\API\UserAPIController::class, "login"]);
Route::post("register", [App\Http\Controllers\API\UserAPIController::class, "register"]);

Route::middleware("localization")->group(function () {
    Route::get("me", [App\Http\Controllers\API\UserAPIController::class, "show"])->middleware('auth:api');
    Route::put("me", [App\Http\Controllers\API\UserAPIController::class, "update"])->middleware('auth:api');

    // Public API routes - protected by API key authentication
    Route::resource('currencies', App\Http\Controllers\API\currencyAPIController::class)
        ->except(['create', 'edit',"store","update","destroy"])
        ->middleware(['api.key:currencies:read', 'api.rate_limit']);

    Route::resource('country-available', App\Http\Controllers\API\CountryAvaillableAPIController::class)
        ->except(['create', 'edit',"store","update","destroy"])
        ->middleware(['api.key:countries:read', 'api.rate_limit']);

    Route::resource('fees', App\Http\Controllers\API\FeesAPIController::class)
        ->except(['create', 'edit',"store","update","destroy"])
        ->middleware(['api.key:fees:read', 'api.rate_limit']);


    // V1 API routes - protected by both OAuth (client_nuage) and API key authentication
    Route::group(['prefix' => 'v1'], function () {

        // Wallet operations - require wallets:read or wallets:write scope
        Route::post("app-wallets",[App\Http\Controllers\API\PayinController::class,"walletsBalance"])
            ->middleware(['client_nuage', 'api.key:wallets:read', 'api.rate_limit']);
        Route::post("app-transaction",[App\Http\Controllers\API\PayinController::class,"appTransaction"])
            ->middleware(['client_nuage', 'api.key:payments:write', 'api.rate_limit']);
        Route::post("check-balance",[App\Http\Controllers\API\PayinController::class,"checkWalletBalance"])
            ->middleware(['client_nuage', 'api.key:wallets:read', 'api.rate_limit']);

        // Mobile payment operations - require payments:write scope
        Route::post("make-mobile-payment",[App\Http\Controllers\API\PayinController::class,"makePayment"])
            ->middleware(['client_nuage', 'api.key:payments:write', 'api.rate_limit']);
        Route::post("check-mobile-payment",[App\Http\Controllers\API\PayinController::class,"checkPayment"])
            ->middleware(['client_nuage', 'api.key:payments:read', 'api.rate_limit']);
        Route::post("make-mobile-payout",[App\Http\Controllers\API\PayinController::class,"payout"])
            ->middleware(['client_nuage', 'api.key:payments:write', 'api.rate_limit']);
        Route::post("check-mobile-payout",[App\Http\Controllers\API\PayinController::class,"checkPayout"])
            ->middleware(['client_nuage', 'api.key:payments:read', 'api.rate_limit']);

        // Bank payment operations - require payments:write scope
        Route::get("get-bank-code",[App\Http\Controllers\API\BankAccountPaymentController::class,"getBankCode"])
            ->middleware(['api.key:payments:read', 'api.rate_limit']);
        Route::post("make-bank-payment",[App\Http\Controllers\API\BankAccountPaymentController::class,"makePayment"])
            ->middleware(['client_nuage', 'api.key:payments:write', 'api.rate_limit']);
        Route::post("check-bank-payment",[App\Http\Controllers\API\BankAccountPaymentController::class,"checkPayment"])
            ->middleware(['client_nuage', 'api.key:payments:read', 'api.rate_limit']);
        Route::post("make-bank-payout",[App\Http\Controllers\API\BankAccountPaymentController::class,"payout"])
            ->middleware(['client_nuage', 'api.key:payments:write', 'api.rate_limit']);
        Route::post("check-bank-payout",[App\Http\Controllers\API\BankAccountPaymentController::class,"checkPayout"])
            ->middleware(['client_nuage', 'api.key:payments:read', 'api.rate_limit']);
        Route::post("verify-account",[App\Http\Controllers\API\BankAccountPaymentController::class,"verifyBankAccount"])
            ->middleware(['client_nuage', 'api.key:payments:read', 'api.rate_limit']);

    });
});

// BRIDGE API Routes - for testing and integration (protected by API key)
Route::prefix('bridge')->middleware(['api.key', 'api.rate_limit'])->group(function () {
    // Wallets - read operations
    Route::get('wallets', [App\Http\Controllers\API\BridgeController::class, 'getWallets'])
        ->middleware('api.key:wallets:read');
    Route::get('wallets/total-balances', [App\Http\Controllers\API\BridgeController::class, 'getTotalBalances'])
        ->middleware('api.key:wallets:read');
    Route::get('wallets/{walletId}/history', [App\Http\Controllers\API\BridgeController::class, 'getWalletTransactionHistory'])
        ->middleware('api.key:wallets:read');

    // Customers
    Route::get('customers', [App\Http\Controllers\API\BridgeController::class, 'getCustomers'])
        ->middleware('api.key:wallets:read');
    Route::get('customers/{customerId}/wallets', [App\Http\Controllers\API\BridgeController::class, 'getCustomerWallets'])
        ->middleware('api.key:wallets:read');
    Route::post('customers/{customerId}/wallets', [App\Http\Controllers\API\BridgeController::class, 'createWallet'])
        ->middleware('api.key:wallets:write');

    // Transfers
    Route::get('transfers', [App\Http\Controllers\API\BridgeController::class, 'listTransfers'])
        ->middleware('api.key:payments:read');
    Route::post('transfers', [App\Http\Controllers\API\BridgeController::class, 'createTransfer'])
        ->middleware('api.key:payments:write');
    Route::get('transfers/{transferId}', [App\Http\Controllers\API\BridgeController::class, 'getTransfer'])
        ->middleware('api.key:payments:read');

    // Exchange Rates
    Route::get('exchange-rates', [App\Http\Controllers\API\BridgeController::class, 'getExchangeRates'])
        ->middleware('api.key:currencies:read');
});

// VALR API Routes - for testing and integration (protected by API key)
Route::prefix('valr')->middleware(['api.key', 'api.rate_limit'])->group(function () {
    // Market data (public endpoints) - read-only access
    Route::get('market-summary', [App\Http\Controllers\API\ValrController::class, 'getMarketSummary'])
        ->middleware('api.key:currencies:read');
    Route::get('currencies', [App\Http\Controllers\API\ValrController::class, 'getCurrencies'])
        ->middleware('api.key:currencies:read');
    Route::get('currency-pairs', [App\Http\Controllers\API\ValrController::class, 'getCurrencyPairs'])
        ->middleware('api.key:currencies:read');
    Route::get('market-summary/{currencyPair}', [App\Http\Controllers\API\ValrController::class, 'getMarketSummaryForCurrencyPair'])
        ->middleware('api.key:currencies:read');
    Route::get('order-book/{currencyPair}', [App\Http\Controllers\API\ValrController::class, 'getOrderBook'])
        ->middleware('api.key:currencies:read');
    
    // Account endpoints
    Route::get('balances', [App\Http\Controllers\API\ValrController::class, 'getBalances'])
        ->middleware('api.key:wallets:read');
    Route::get('deposit-address/{currency}', [App\Http\Controllers\API\ValrController::class, 'getDepositAddress'])
        ->middleware('api.key:wallets:read');
    
    // Orders
    Route::get('orders/open', [App\Http\Controllers\API\ValrController::class, 'getOpenOrders'])
        ->middleware('api.key:payments:read');
    Route::get('orders/{currencyPair}/orderId/{orderId}', [App\Http\Controllers\API\ValrController::class, 'getOrderStatus'])
        ->middleware('api.key:payments:read');
    Route::post('orders/limit', [App\Http\Controllers\API\ValrController::class, 'placeLimitOrder'])
        ->middleware('api.key:payments:write');
    Route::post('orders/market', [App\Http\Controllers\API\ValrController::class, 'placeMarketOrder'])
        ->middleware('api.key:payments:write');
    Route::delete('orders/cancel', [App\Http\Controllers\API\ValrController::class, 'cancelOrder'])
        ->middleware('api.key:payments:write');
    
    // Simple trading
    Route::post('simple/quote', [App\Http\Controllers\API\ValrController::class, 'getSimpleQuote'])
        ->middleware('api.key:currencies:read');
    Route::post('simple/order', [App\Http\Controllers\API\ValrController::class, 'placeSimpleOrder'])
        ->middleware('api.key:payments:write');

    // Payments
    Route::post('pay', [App\Http\Controllers\API\ValrController::class, 'makePayment'])
        ->middleware('api.key:payments:write');

    // Wallet -> Fiat
    Route::get('bank-accounts/{currencyCode}', [App\Http\Controllers\API\ValrController::class, 'getLinkedBankAccounts'])
        ->middleware('api.key:wallets:read');
    Route::post('fiat/{currencyCode}/withdraw', [App\Http\Controllers\API\ValrController::class, 'makeFiatWithdrawal'])
        ->middleware('api.key:payments:write');
    
    // Wallet -> Crypto
    Route::get('crypto/{currencyCode}/deposit/address', [App\Http\Controllers\API\ValrController::class, 'getCryptoCurrencyWalletAddress'])
        ->middleware('api.key:wallets:read');
    Route::post('crypto/{currencyCode}/withdraw', [App\Http\Controllers\API\ValrController::class, 'makeCryptoWithdrawal'])
        ->middleware('api.key:payments:write');
    Route::get('crypto/service-providers', [App\Http\Controllers\API\ValrController::class, 'getCryptoServiceProviders'])
        ->middleware('api.key:currencies:read');
    Route::get('crypto/{currencyCode}/withdraw-info', [App\Http\Controllers\API\ValrController::class, 'getWithdrawalConfigInfo'])
        ->middleware('api.key:currencies:read');
    Route::get('crypto/withdraw/history', [App\Http\Controllers\API\ValrController::class, 'getCryptoWithdrawalHistory'])
        ->middleware('api.key:payments:read');
    Route::get('crypto/{currencyCode}/withdraw/{withdrawId}/status', [App\Http\Controllers\API\ValrController::class, 'getCryptoWithdrawalStatus'])
        ->middleware('api.key:payments:read');
    Route::get('crypto/address-book', [App\Http\Controllers\API\ValrController::class, 'getCryptoAddressBook'])
        ->middleware('api.key:wallets:read');
    Route::post('crypto/validate-address', [App\Http\Controllers\API\ValrController::class, 'validateWhitelistedAddress'])
        ->middleware('api.key:wallets:read');
});

// Korapay API Routes
Route::prefix('korapay')->middleware(['api.key', 'api.rate_limit'])->group(function () {
    // Balances
    Route::get('balances', [App\Http\Controllers\API\KorapayController::class, 'getBalances'])
        ->middleware('api.key:wallets:read');

    // Bank Account Resolution
    Route::post('payouts/resolve-account', [App\Http\Controllers\API\KorapayController::class, 'resolveBankAccount'])
        ->middleware('api.key:payments:read');

    // Payouts
    Route::post('payouts/transfer', [App\Http\Controllers\API\KorapayController::class, 'makeTransfer'])
        ->middleware('api.key:payments:write');
    Route::get('payouts/status/{reference}', [App\Http\Controllers\API\KorapayController::class, 'getTransferStatus'])
        ->middleware('api.key:payments:read');

    // Banks
    Route::get('misc/banks', [App\Http\Controllers\API\KorapayController::class, 'getBanks'])
        ->middleware('api.key:currencies:read'); // or wallets:read

    // Pay-ins
    Route::post('payins/initialize', [App\Http\Controllers\API\KorapayController::class, 'initializeCheckout'])
        ->middleware('api.key:payments:write');
    Route::get('payins/verify/{reference}', [App\Http\Controllers\API\KorapayController::class, 'verifyTransaction'])
        ->middleware('api.key:payments:read');

    // Webhook (Usually public or requires specific signature verification middleware, 
    // but here adding it under api.key might be wrong if Korapay calls it directly without our API key.
    // Webhooks should essentially be public but signature verified.
    // Valr routes show public endpoints? No, they are all under middleware. 
    // Wait, Bridge/Valr Controller webhooks might be elsewhere?
    // Looking at api.php line 20: Route::post('startbutton-callback'...) is public.
    // Line 39 extension webhooks are public (or custom middleware).
    // So I should probably put the webhook OUTSIDE the api.key middleware group.
});

// Korapay Webhook - Public endpoint (Signature verification inside controller)
Route::post('korapay/webhook', [App\Http\Controllers\API\KorapayController::class, 'handleWebhook']);

// Flutterwave API Routes
Route::prefix('flutterwave')->middleware(['api.key', 'api.rate_limit'])->group(function () {

    // Customers
    Route::post('customers', [App\Http\Controllers\API\FlutterwaveController::class, 'createCustomer'])
        ->middleware('api.key:payments:write');
    Route::get('customers/{customerId}', [App\Http\Controllers\API\FlutterwaveController::class, 'getCustomer'])
        ->middleware('api.key:payments:read');

    // Payment Methods
    Route::post('payment-methods/card', [App\Http\Controllers\API\FlutterwaveController::class, 'createCardPaymentMethod'])
        ->middleware('api.key:payments:write');
    Route::post('payment-methods/momo', [App\Http\Controllers\API\FlutterwaveController::class, 'createMoMoPaymentMethod'])
        ->middleware('api.key:payments:write');

    // Charges (Pay-ins)
    Route::post('charges', [App\Http\Controllers\API\FlutterwaveController::class, 'createCharge'])
        ->middleware('api.key:payments:write');
    Route::put('charges/{chargeId}/authorize', [App\Http\Controllers\API\FlutterwaveController::class, 'authorizeCharge'])
        ->middleware('api.key:payments:write');
    Route::get('charges/{chargeId}', [App\Http\Controllers\API\FlutterwaveController::class, 'getCharge'])
        ->middleware('api.key:payments:read');

    // Direct Transfers (Payouts)
    Route::post('transfers/resolve-bank', [App\Http\Controllers\API\FlutterwaveController::class, 'resolveBankAccount'])
        ->middleware('api.key:payments:read');
    Route::post('transfers', [App\Http\Controllers\API\FlutterwaveController::class, 'createTransfer'])
        ->middleware('api.key:payments:write');
    Route::get('transfers/{transferId}', [App\Http\Controllers\API\FlutterwaveController::class, 'getTransfer'])
        ->middleware('api.key:payments:read');

    // Refunds
    Route::post('refunds', [App\Http\Controllers\API\FlutterwaveController::class, 'createRefund'])
        ->middleware('api.key:payments:write');
    Route::get('refunds', [App\Http\Controllers\API\FlutterwaveController::class, 'listRefunds'])
        ->middleware('api.key:payments:read');
    Route::get('refunds/{refundId}', [App\Http\Controllers\API\FlutterwaveController::class, 'getRefund'])
        ->middleware('api.key:payments:read');
});

// Flutterwave Webhook - Public endpoint (HMAC signature verified inside controller)
Route::post('flutterwave/webhook', [App\Http\Controllers\API\FlutterwaveController::class, 'handleWebhook']);

//
//Route::post("dish", function () {
//    $achat = \App\Models\Achat::find(15);
//
//
//
//    PayOutFailureEvent::dispatch($achat);
//});


//Route::resource('companies', App\Http\Controllers\API\CompanyAPIController::class)
//    ->except(['create', 'edit']);
//
//Route::resource('app-fees', App\Http\Controllers\API\AppFeeAPIController::class)
//    ->except(['create', 'edit']);

// Note: API Key Management Routes are defined earlier in this file (around line 29)
// using auth:api middleware for Passport authentication

// API Scopes endpoint (requires Passport authentication)
Route::middleware('auth:api')->get('api-scopes', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\ApiScope::all(['id', 'name', 'description', 'resource', 'action']),
    ]);
});
