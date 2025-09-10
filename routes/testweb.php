<?php
use Illuminate\Support\Facades\Route;

Route::get("/test/fincra-business", [\App\Http\Controllers\TestingController::class, 'getFincraBusinessID']);

Route::get("/test/fincra-banks", [\App\Http\Controllers\TestingController::class, 'getFincraBanks']);
