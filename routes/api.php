<?php

use App\Events\PayOutFailureEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'extension'], function () {
    Route::post('activate', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'activate']);
    Route::post('deactivate', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'deactivate']);
    Route::post('webhook', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'webhook']);
    Route::post('rotate', [\App\Http\Controllers\API\Rehive\WebhookController::class, 'rotate']);
});


Route::middleware("localization")->group(function () {
//    Route::get("me",[App\Http\Controllers\API\UserAPIController::class,"show"])->middleware('auth:api');
//    Route::put("me",[App\Http\Controllers\API\UserAPIController::class,"update"])->middleware('auth:api');
//    Route::post("login",[App\Http\Controllers\API\UserAPIController::class,"login"]);

    Route::resource('currencies', App\Http\Controllers\API\currencyAPIController::class)
        ->except(['create', 'edit',"store","update","destroy"]);

    Route::resource('country-available', App\Http\Controllers\API\CountryAvaillableAPIController::class)
        ->except(['create', 'edit',"store","update","destroy"]);

    Route::resource('fees', App\Http\Controllers\API\FeesAPIController::class)
        ->except(['create', 'edit',"store","update","destroy"]);


    Route::group(['prefix' => 'v1'], function () {

        Route::post("app-wallets",[App\Http\Controllers\API\PayinController::class,"walletsBalance"])->middleware(['client_nuage']);
        Route::post("app-transaction",[App\Http\Controllers\API\PayinController::class,"appTransaction"])->middleware(['client_nuage']);
        Route::post("check-balance",[App\Http\Controllers\API\PayinController::class,"checkWalletBalance"])->middleware(['client_nuage']);



        Route::post("make-mobile-payment",[App\Http\Controllers\API\PayinController::class,"makePayment"])->middleware(['client_nuage']);
        Route::post("check-mobile-payment",[App\Http\Controllers\API\PayinController::class,"checkPayment"])->middleware(['client_nuage']);
        Route::post("make-mobile-payout",[App\Http\Controllers\API\PayinController::class,"payout"])->middleware(['client_nuage']);
        Route::post("check-mobile-payout",[App\Http\Controllers\API\PayinController::class,"checkPayout"])->middleware(['client_nuage']);


        Route::get("get-bank-code",[App\Http\Controllers\API\BankAccountPaymentController::class,"getBankCode"]);
        Route::post("make-bank-payment",[App\Http\Controllers\API\BankAccountPaymentController::class,"makePayment"])->middleware(['client_nuage']);
        Route::post("check-bank-payment",[App\Http\Controllers\API\BankAccountPaymentController::class,"checkPayment"])->middleware(['client_nuage']);
        Route::post("make-bank-payout",[App\Http\Controllers\API\BankAccountPaymentController::class,"payout"])->middleware(['client_nuage']);
        Route::post("check-bank-payout",[App\Http\Controllers\API\BankAccountPaymentController::class,"checkPayout"])->middleware(['client_nuage']);
        Route::post("verify-account",[App\Http\Controllers\API\BankAccountPaymentController::class,"verifyBankAccount"])->middleware(['client_nuage']);

    });
});

// BRIDGE API Routes - for testing and integration
Route::prefix('bridge')->group(function () {
    // Wallets
    Route::get('wallets', [App\Http\Controllers\API\BridgeController::class, 'getWallets']);
    Route::get('wallets/total-balances', [App\Http\Controllers\API\BridgeController::class, 'getTotalBalances']);
    Route::get('wallets/{walletId}/history', [App\Http\Controllers\API\BridgeController::class, 'getWalletTransactionHistory']);

    // Customers
    Route::get('customers', [App\Http\Controllers\API\BridgeController::class, 'getCustomers']);
    Route::get('customers/{customerId}/wallets', [App\Http\Controllers\API\BridgeController::class, 'getCustomerWallets']);
    Route::post('customers/{customerId}/wallets', [App\Http\Controllers\API\BridgeController::class, 'createWallet']);

    // Transfers
    Route::get('transfers', [App\Http\Controllers\API\BridgeController::class, 'listTransfers']);
    Route::post('transfers', [App\Http\Controllers\API\BridgeController::class, 'createTransfer']);
    Route::get('transfers/{transferId}', [App\Http\Controllers\API\BridgeController::class, 'getTransfer']);

    // Exchange Rates
    Route::get('exchange-rates', [App\Http\Controllers\API\BridgeController::class, 'getExchangeRates']);
});

// VALR API Routes - for testing and integration
Route::prefix('valr')->group(function () {
    // Market data (public endpoints)
    Route::get('market-summary', [App\Http\Controllers\API\ValrController::class, 'getMarketSummary']);
    Route::get('currencies', [App\Http\Controllers\API\ValrController::class, 'getCurrencies']);
    Route::get('currency-pairs', [App\Http\Controllers\API\ValrController::class, 'getCurrencyPairs']);
    Route::get('market-summary/{currencyPair}', [App\Http\Controllers\API\ValrController::class, 'getMarketSummaryForCurrencyPair']);
    Route::get('order-book/{currencyPair}', [App\Http\Controllers\API\ValrController::class, 'getOrderBook']);
    
    // Account endpoints (protected)
    Route::get('balances', [App\Http\Controllers\API\ValrController::class, 'getBalances']);
    Route::get('deposit-address/{currency}', [App\Http\Controllers\API\ValrController::class, 'getDepositAddress']);
    
    // Orders
    Route::get('orders/open', [App\Http\Controllers\API\ValrController::class, 'getOpenOrders']);
    Route::get('orders/{currencyPair}/orderId/{orderId}', [App\Http\Controllers\API\ValrController::class, 'getOrderStatus']);
    Route::post('orders/limit', [App\Http\Controllers\API\ValrController::class, 'placeLimitOrder']);
    Route::post('orders/market', [App\Http\Controllers\API\ValrController::class, 'placeMarketOrder']);
    Route::delete('orders/cancel', [App\Http\Controllers\API\ValrController::class, 'cancelOrder']);
    
    // Simple trading
    Route::post('simple/quote', [App\Http\Controllers\API\ValrController::class, 'getSimpleQuote']);
    Route::post('simple/order', [App\Http\Controllers\API\ValrController::class, 'placeSimpleOrder']);

    // Payments
    Route::post('pay', [App\Http\Controllers\API\ValrController::class, 'makePayment']);

    // Wallet -> Fiat
    Route::get('bank-accounts/{currencyCode}', [App\Http\Controllers\API\ValrController::class, 'getLinkedBankAccounts']);
    Route::post('fiat/{currencyCode}/withdraw', [App\Http\Controllers\API\ValrController::class, 'makeFiatWithdrawal']);
    
    // Wallet -> Crypto
    Route::get('crypto/{currencyCode}/deposit/address', [App\Http\Controllers\API\ValrController::class, 'getCryptoCurrencyWalletAddress']);
    Route::post('crypto/{currencyCode}/withdraw', [App\Http\Controllers\API\ValrController::class, 'makeCryptoWithdrawal']);
    Route::get('crypto/service-providers', [App\Http\Controllers\API\ValrController::class, 'getCryptoServiceProviders']);
    Route::get('crypto/{currencyCode}/withdraw-info', [App\Http\Controllers\API\ValrController::class, 'getWithdrawalConfigInfo']);
    Route::get('crypto/withdraw/history', [App\Http\Controllers\API\ValrController::class, 'getCryptoWithdrawalHistory']);
    Route::get('crypto/{currencyCode}/withdraw/{withdrawId}/status', [App\Http\Controllers\API\ValrController::class, 'getCryptoWithdrawalStatus']);
    Route::get('crypto/address-book', [App\Http\Controllers\API\ValrController::class, 'getCryptoAddressBook']);
    Route::post('crypto/validate-address', [App\Http\Controllers\API\ValrController::class, 'validateWhitelistedAddress']);
});

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
