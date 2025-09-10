<?php

namespace App\Http\Controllers;

use App\Services\Fincra\FincraService;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function getFincraBusinessID(Request $request) {

        $fincraService = new FincraService();
        dd($fincraService->getBusinessId());
    }

    public function getFincraBanks(Request$request) {
        $fincraService = new FincraService();
        dd($fincraService->fetchBanks());
    }
}
