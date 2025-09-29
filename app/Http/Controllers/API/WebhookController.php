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
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Client\RequestException;
use Exception;
use \App\Jobs\ProcessRehiveWebhook;

class WebhookController extends Controller
{
    private const REHIVE_API_BASE_URL = 'https://api.rehive.com/3';
    private const REHIVE_AUTH_URL = self::REHIVE_API_BASE_URL . '/auth/';
    private const REHIVE_COMPANY_URL = self::REHIVE_API_BASE_URL . '/company/';
    private const REHIVE_SIGNATURE_HEADER = 'X-Rehive-Signature';
    
    private function validateRequestAndGetToken(Request $request): string
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $token = $request->input('token');

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $token,
        ])->get(self::REHIVE_AUTH_URL);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $token;
    }

    private function getRehiveCompanyName(string $token): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $token,
        ])->get(self::REHIVE_COMPANY_URL);

        if ($response->successful()) {
            return $response->json('data.name');
        }

        Log::error('Failed to fetch Rehive company data', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    public function activate(Request $request)
    {
        try {
            $serviceToken = $this->validateRequestAndGetToken($request);
            $company = $this->getRehiveCompanyName($serviceToken);

            if (is_null($company)) {
                Log::error('Could not retrieve company name from Rehive');
                return response()->json(['status' => 'error', 'message' => 'Could not retrieve company name from Rehive'], 400);
            }

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
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rehive service token validation failed',
                'rehive_status' => $e->response->status(),
                'rehive_body' => $e->response->json()
            ], 401);
        } catch (Exception $e) {
            Log::error('Error activating Rehive service: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function deactivate(Request $request)
    {
        $purge = false;

        try {
            $token = $this->validateRequestAndGetToken($request);
            $company = $this->getRehiveCompanyName($token);
            $rehiveServiceToken = ServiceToken::where('company', $company)->first();

            if (!$rehiveServiceToken) {
                return response()->json(['status' => 'error', 'message' => 'Token not found'], 404);
            }

            if ($purge) {
                $rehiveServiceToken->delete();
            } else {
                $rehiveServiceToken->update(['activated' => false]);
            }

            return response()->json(['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rehive service token validation failed',
                'rehive_status' => $e->response->status(),
                'rehive_body' => $e->response->json()
            ], 401);
        } catch (Exception $e) {
            Log::error('Error deactivating Rehive service: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function rotate(Request $request)
    {
        try {
            $newToken = $this->validateRequestAndGetToken($request);
            $company = $this->getRehiveCompanyName($newToken);

            $rehiveServiceToken = ServiceToken::where('company', $company)->first();

            if (!$rehiveServiceToken) {
                return response()->json(['status' => 'error', 'message' => 'Company not found'], 404);
            }

            $rehiveServiceToken->update(['token' => $newToken]);

            return response()->json(['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rehive service token validation failed',
                'rehive_status' => $e->response->status(),
                'rehive_body' => $e->response->json()
            ], 401);
        } catch (Exception $e) {
            Log::error('Error rotating Rehive service token: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $token = $this->validateRequestAndGetToken($request);
            $companyIdentifier = $this->getRehiveCompanyName($token);
            $secret = $request->header(self::REHIVE_SIGNATURE_HEADER);

            if (!$secret) {
                return response()->json(['status' => 'error', 'message' => 'Missing secret / signature'], 400);
            }

            $rehiveServiceToken = ServiceToken::where('company', $companyIdentifier)->first();

            if (!$rehiveServiceToken) {
                return response()->json(['status' => 'error', 'message' => 'Service Token not found'], 404);
            }

            if (!$rehiveServiceToken->activated) {
                return response()->json(['status' => 'error', 'message' => 'Service not activated'], 403);
            }

            if (!hash_equals($rehiveServiceToken->webhook_secret, $secret)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }

            // Dispatch a job to process the webhook
            ProcessRehiveWebhook::dispatch($request->all());

            return response()->json(['status' => 'success']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (RequestException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rehive service token validation failed',
                'rehive_status' => $e->response->status(),
                'rehive_body' => $e->response->json()
            ], 401);
        } catch (Exception $e) {
            Log::error('Error processing Rehive webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}
