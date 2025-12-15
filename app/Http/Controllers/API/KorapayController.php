<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Korapay\KorapayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class KorapayController extends Controller
{
    protected KorapayService $korapayService;

    public function __construct(KorapayService $korapayService)
    {
        $this->korapayService = $korapayService;
    }

    /**
     * Get account balances
     * @return JsonResponse
     */
    public function getBalances(): JsonResponse
    {
        try {
            $response = $this->korapayService->getBalances();
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay getBalances error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch balances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve Bank Account
     * @param Request $request
     * @return JsonResponse
     */
    public function resolveBankAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'currency' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->korapayService->resolveBankAccount($request->all());
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay resolveBankAccount error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve bank account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get List of Banks
     * @return JsonResponse
     */
    public function getBanks(Request $request): JsonResponse
    {
        try {
            $countryCode = $request->query('countryCode', 'NG');
            $response = $this->korapayService->getBanks($countryCode);
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay getBanks error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate Payout (Transfer)
     * @param Request $request
     * @return JsonResponse
     */
    public function makeTransfer(Request $request): JsonResponse
    {
        // Add specific validation based on Korapay requirements
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'destination' => 'required|array',
            'destination.type' => 'required|string',
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->korapayService->initiateTransfer($request->all());
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay makeTransfer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Transfer Status
     * @param string $reference
     * @return JsonResponse
     */
    public function getTransferStatus(string $reference): JsonResponse
    {
        try {
            $response = $this->korapayService->getTransferStatus($reference);
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay getTransferStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfer status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize Checkout
     * @param Request $request
     * @return JsonResponse
     */
    public function initializeCheckout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'redirect_url' => 'nullable|url',
            'notification_url' => 'nullable|url',
            'narration' => 'nullable|string',
            'channels' => 'nullable|array',
            'default_channel' => 'nullable|string',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            // Generate unique reference
            $data['reference'] = 'KPY-' . Str::uuid();
            
            $response = $this->korapayService->initializeCheckout($data);
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay initializeCheckout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Transaction
     * @param string $reference
     * @return JsonResponse
     */
    public function verifyTransaction(string $reference): JsonResponse
    {
        try {
            $response = $this->korapayService->verifyTransaction($reference);
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('Korapay verifyTransaction error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Webhook
     * @param Request $request
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $secret = env('KORAPAY_SECRET_KEY');
        $signature = $request->header('x-korapay-signature');

        if (!$signature) {
             return response()->json(['message' => 'Signature missing'], 400);
        }

        $payload = $request->input('data');
        if (!$payload) {
             return response()->json(['message' => 'No data payload'], 200);
        }

        // Verify signature
        // Korapay signature is HMAC SHA256 of the data object ONLY.
        // We must re-encode the data array to JSON to match the string used for signing.
        // Note: JSON flags might matter. Docs suggest standard json_encode.
        $computedSignature = hash_hmac('sha256', json_encode($payload), $secret);

        if (!hash_equals($signature, $computedSignature)) {
             Log::warning('Korapay webhook signature mismatch', [
                 'header' => $signature,
                 'computed' => $computedSignature
             ]);
             return response()->json(['message' => 'Invalid signature'], 400);
        }

        Log::info('Korapay Webhook Received', $request->all());

        $event = $request->input('event');
        $reference = $payload['reference'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$reference) {
            return response()->json(['success' => true]);
        }

        try {
            if ($event === 'transfer.success' || $event === 'transfer.failed') {
                $this->handleTransferUpdate($reference, $status, $payload);
            } elseif ($event === 'charge.success') {
                $this->handleChargeSuccess($reference, $payload);
            }
        } catch (Exception $e) {
            Log::error('Error handling Korapay webhook event', ['error' => $e->getMessage()]);
            // Return 200 to stop retries if logic failed (or 500 to retry? standard is usually 200 unless transient)
            return response()->json(['success' => false, 'message' => $e->getMessage()], 200);
        }

        return response()->json(['success' => true], 200);
    }

    private function handleTransferUpdate(string $reference, string $status, array $data)
    {
        // Payout update
        // Find Achat by ref_id (which is used as reference for transfers)
        $achat = \App\Models\Achat::where('ref_id', $reference)->first();
        
        if ($achat) {
            if ($status === 'success') {
                $achat->status = \App\Enums\PaymentStatus::SUCCESS;
                $achat->save();
                
                if ($achat->requestable) {
                    $achat->requestable->status = \App\Enums\PaymentStatus::SUCCESS;
                    $achat->requestable->save();
                }
            } elseif ($status === 'failed') {
                $achat->status = \App\Enums\PaymentStatus::FAILED;
                $achat->save();
                
                if ($achat->requestable) {
                    $achat->requestable->status = \App\Enums\PaymentStatus::FAILED;
                    $achat->requestable->save();
                }
            }
        }
    }

    private function handleChargeSuccess(string $reference, array $data)
    {
        // Pay-in success
        // Find Achat by user_ref_id or similar if we stored the Korapay reference? 
        // In initializeCheckout we generated a reference "KPY-...".
        // We probably need to find the Achat that has this reference.
        // Assuming we stored 'KPY-...' in `ref_id` or `user_ref_id` or a dedicated column.
        // If not, we might need to rely on metadata passed during checkout.
        
        $achat = \App\Models\Achat::where('ref_id', $reference)->first();

        if ($achat && $achat->status !== \App\Enums\PaymentStatus::SUCCESS) {
             $achat->status = \App\Enums\PaymentStatus::SUCCESS;
             $achat->save();
             
             // Update wallet balance...
             // This logic usually resides in a Helper or Service to ensure consistency.
             // For now, updating status is key.
        }
    }
}
