<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


Auth::routes(['verify' => true]);


Route::group(['prefix' => 'admin',"middleware"=>['verified','auth', 'ensure.api_key']], function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('users.profile');
    Route::get('/profile/edit', [App\Http\Controllers\UserController::class, 'editProfile'])->name('users.profile.edit');
    Route::post('/change-password', [App\Http\Controllers\UserController::class, 'changePassword'])->name('users.change.password');
    Route::post('/profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('users.profile.update');
    Route::get('/api-key', [App\Http\Controllers\UserController::class, 'apiKey'])->name('users.api_key');
    Route::post('/api-key/generate', [App\Http\Controllers\UserController::class, 'generateApiKey'])->name('users.api_key.generate');
    Route::delete('/api-key/{id}', [App\Http\Controllers\UserController::class, 'revokeApiKey'])->name('users.api_key.revoke');
    Route::resource('users', App\Http\Controllers\UserController::class)->middleware("is_admin");
    Route::resource('currencies', App\Http\Controllers\CurrencyController::class)->middleware("is_admin");
    Route::resource('system-legers', App\Http\Controllers\SystemLedgerController::class)->middleware("is_admin");
    Route::resource('achats', App\Http\Controllers\AchatController::class);
    Route::resource('fincra-banks', App\Http\Controllers\FincraBankController::class)->middleware("is_admin");
    Route::resource('fincra-bank-accounts', App\Http\Controllers\FincraBankAccountController::class)->middleware("is_admin");
    Route::resource('fiat-wallets', App\Http\Controllers\WalletController::class);
    Route::resource('crypto-wallets', App\Http\Controllers\CryptoWalletController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/crypto-wallets/{id}/fund', [App\Http\Controllers\CryptoWalletController::class, 'fund'])->name('crypto-wallets.fund');
    Route::post('/crypto-wallets/{id}/send', [App\Http\Controllers\CryptoWalletController::class, 'send'])->name('crypto-wallets.send');
    Route::resource('custom-fees', App\Http\Controllers\AppFeeController::class)->except(['create',"index","show","destroy"])->middleware("is_admin");
    Route::get('/custom-fees/create/{company_id}/{id}', [App\Http\Controllers\AppFeeController::class, 'create'])->name('custom-fees.create')->middleware("is_admin");

    Route::resource('transactions', App\Http\Controllers\TransactionController::class);
    Route::resource('apps', App\Http\Controllers\ClientController::class)->except(['create',"store","destroy"]);
    Route::get('/fund-fiat-wallet/{id}', [App\Http\Controllers\ClientController::class, 'fundWalletView'])->name('apps.fund_fiat_wallet');
    Route::post('/fund-fiat-wallet/{id}', [App\Http\Controllers\ClientController::class, 'fundWallet'])->name('apps.fund_fiat_wallet_post');
    Route::get('/transaction-processing/{achatId}', [App\Http\Controllers\ClientController::class, 'showTransactionProcessing'])->name('apps.transaction_processing');
    Route::get('/payout-processing/{achatId}', [App\Http\Controllers\ClientController::class, 'showPayoutProcessing'])->name('apps.payout_processing');
    Route::get('/check-transaction-status/{achatId}', [App\Http\Controllers\ClientController::class, 'checkTransactionStatus'])->name('apps.check_transaction_status');
    Route::post('/dispatch-background-job/{achatId}', [App\Http\Controllers\ClientController::class, 'dispatchBackgroundJob'])->name('apps.dispatch_background_job');
    Route::get('/withdraw/{id}', [App\Http\Controllers\ClientController::class, 'withdrawView'])->name('apps.withdraw');
    Route::post('/withdraw/{id}', [App\Http\Controllers\ClientController::class, 'withdraw'])->name('apps.withdraw_post');
    Route::post('/app-generate/{id}', [App\Http\Controllers\ClientController::class, 'regenerate'])->name('apps.generate');
    Route::post('change-main-wallet', [App\Http\Controllers\ClientController::class, 'changeWallet'])->name('apps.change_wallet');
    Route::post('/app-show-secret/{id}', [App\Http\Controllers\ClientController::class, 'showSecret'])->name('apps.show_secret');
    Route::resource('companies', App\Http\Controllers\CompanyController::class)->except(['create',"store","destroy"]);
    Route::resource('country-availlables', App\Http\Controllers\CountryAvaillableController::class)->middleware("is_admin");
    Route::resource('fees', App\Http\Controllers\FeesController::class)->middleware("is_admin");
    Route::resource('start-button-banks', App\Http\Controllers\StartButtonBankController::class);

    Route::resource('exchange-rate-margins', App\Http\Controllers\ExchangeRateMarginController::class)->middleware("is_admin");
    Route::resource('exchange-fee-margins', App\Http\Controllers\ExchangeFeeMarginController::class)->middleware("is_admin");
    Route::resource('exchange-requests', App\Http\Controllers\ExchangeRequestController::class)->except(['create', 'edit','update',"destroy"]);
    Route::resource('custom-exchange-rate-margins', App\Http\Controllers\CustomExchangeRateMarginController::class)->except(['create',"index","show","destroy"])->middleware("is_admin");

    Route::get('/custom-exchange-rate-margins/create/{company_id}/{id}', [App\Http\Controllers\CustomExchangeRateMarginController::class, 'create'])->name('custom-exchange-rate-margins.create')->middleware("is_admin");
    Route::get('/custom-exchange-rate-margins/{id}', [App\Http\Controllers\CustomExchangeRateMarginController::class, 'index'])->name('custom-exchange-rate-margins.index')->middleware("is_admin");

    Route::get('/exchange-requests/make/{id}', [App\Http\Controllers\ExchangeRequestController::class, 'create'])->name('exchange-request.create');

});


Route::get('/doc', function () {
    return view('doc');
})->name("doc");


//Route::get('/create', function () {
//
//    $user = new User();
//    $user->name= "user";
//    $user->business_name="USER";
//    $user->phone_number = "+237680355391";
//    $user->email = "aubin@gmail.com";
//    $user->business_type = "Fintech";
//    $user->password = "12345678";
//    $user->country_code = "cmr";
//    $user->website ="app.com";
//
//    $user->save();
//
//
//    return Response::json($user);
//});
