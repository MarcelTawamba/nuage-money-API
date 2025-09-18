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


Route::post('startbutton-callback', [\App\Http\Controllers\API\StartButtonWebHookController::class, 'handleWebhook']);

Route::group(['prefix' => 'rehive'], function () {
    Route::post('activate', [\App\Http\Controllers\API\RehiveWebhookController::class, 'activate']);
    Route::post('deactivate', [\App\Http\Controllers\API\RehiveWebhookController::class, 'deactivate']);
    Route::post('webhook', [\App\Http\Controllers\API\RehiveWebhookController::class, 'webhook']);
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
