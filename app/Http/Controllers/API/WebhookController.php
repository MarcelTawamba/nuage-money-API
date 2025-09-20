<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ServiceToken;
use App\Services\RehiveOfframpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    private function validateRehiveToken(string $token): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Token ' . $token,
        ])->get('https://api.rehive.com/3/auth/');
    }

    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $serviceToken = $request->input('service_token');
        Log::info('Activate request received with token: ' . $serviceToken);

        try {
            $response = $this->validateRehiveToken($serviceToken);

            if ($response->failed()) {
                Log::error('Rehive service token validation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid service token'], 401);
            }

            $company = $response->json('data.company');
            $webhook_secret = Str::random(32);

            ServiceToken::updateOrCreate(
                ['company' => $company],
                [
                    'token' => $serviceToken,
                    'activated' => true,
                    'webhook_secret' => $webhook_secret,
                ]
            );

            return response()->json(['status' => 'success', 'secret' => $webhook_secret]);
        } catch (\Exception $e) {
            Log::error('Error activating Rehive service: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function deactivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'purge' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $token = $request->input('token');
        $purge = $request->input('purge', false);

        try {
            $response = $this->validateRehiveToken($token);

            if ($response->failed()) {
                Log::error('Rehive service token validation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid service token'], 401);
            }

            $rehiveServiceToken = ServiceToken::where('token', $token)->first();

            if (!$rehiveServiceToken) {
                return response()->json(['status' => 'error', 'message' => 'Token not found'], 404);
            }

            if ($purge) {
                $rehiveServiceToken->delete();
            } else {
                $rehiveServiceToken->update(['activated' => false]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deactivating Rehive service: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function rotate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $newToken = $request->input('token');

        try {
            $response = $this->validateRehiveToken($newToken);

            if ($response->failed()) {
                Log::error('Rehive service token validation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid service token'], 401);
            }

            $company = $response->json('data.company');

            $rehiveServiceToken = ServiceToken::where('company', $company)->first();

            if (!$rehiveServiceToken) {
                return response()->json(['status' => 'error', 'message' => 'Company not found'], 404);
            }

            $rehiveServiceToken->update(['token' => $newToken]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error rotating Rehive service token: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function webhook(Request $request)
    {
        $companyIdentifier = $request->input('company');
        $secret = $request->header('X-Rehive-Signature');

        if (!$companyIdentifier || !$secret) {
            return response()->json(['status' => 'error', 'message' => 'Missing company or signature'], 400);
        }

        $rehiveServiceToken = ServiceToken::where('company', $companyIdentifier)->first();

        if (!$rehiveServiceToken || !$rehiveServiceToken->activated) {
            return response()->json(['status' => 'error', 'message' => 'Company not found or not activated'], 404);
        }

        if (!hash_equals($rehiveServiceToken->webhook_secret, $secret)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        // Dispatch a job to process the webhook
        \App\Jobs\ProcessRehiveWebhook::dispatch($request->all());

        return response()->json(['status' => 'success']);
    }
}
