<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RehiveServiceToken;
use App\Services\RehiveOfframpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RehiveWebhookController extends Controller
{
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            RehiveServiceToken::truncate();

            RehiveServiceToken::create([
                'token' => $request->input('service_token'),
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error activating Rehive service: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function deactivate()
    {
        try {
            RehiveServiceToken::truncate();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deactivating Rehive service: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function webhook(Request $request, RehiveOfframpService $rehiveOfframpService)
    {
        try {
            Log::info('Rehive webhook received:', $request->all());

            if ($request->input('event') === 'transaction.execute') {
                $rehiveOfframpService->processTransaction($request->all());
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error processing Rehive webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}
