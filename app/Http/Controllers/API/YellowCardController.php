<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\YellowCard\YellowCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class YellowCardController extends Controller
{
    protected YellowCardService $yellowCardService;

    public function __construct(YellowCardService $yellowCardService)
    {
        $this->yellowCardService = $yellowCardService;
    }

    /**
     * Get available payment channels
     * @param Request $request
     * @return JsonResponse
     */
    public function getChannels(Request $request): JsonResponse
    {
        try {
            $country = $request->input('country');
            $forceRefresh = $request->boolean('refresh', false);
            $response = $this->yellowCardService->getChannels($country, $forceRefresh);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getChannels error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch channels',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get channels suitable for widget operations (filtered)
     * @param Request $request
     * @return JsonResponse
     */
    public function getWidgetChannels(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'nullable|string|max:10',
            'transactionType' => 'nullable|string|in:Buy,Sell'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currency = $request->input('currency');
            $transactionType = $request->input('transactionType');
            
            $channels = $this->yellowCardService->getWidgetChannels($currency, $transactionType);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'channels' => $channels,
                    'count' => count($channels)
                ]
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getWidgetChannels error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch widget channels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exchange rates
     * @param Request $request
     * @return JsonResponse
     */
    public function getExchangeRates(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'nullable|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currency = $request->input('currency');
            $currency = $currency ? strtoupper($currency) : null;
            
            $response = $this->yellowCardService->getExchangeRates($currency);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getExchangeRates error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch exchange rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment networks
     * @param Request $request
     * @return JsonResponse
     */
    public function getNetworks(Request $request): JsonResponse
    {
        try {
            $country = $request->input('country');
            $response = $this->yellowCardService->getNetworks($country);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getNetworks error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch networks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account information
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccount(Request $request): JsonResponse
    {
        try {
            $response = $this->yellowCardService->getAccount();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getAccount error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch account information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payments list
     * @param Request $request
     * @return JsonResponse
     */
    public function getPayments(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'startDate', 'endDate', 'limit', 'offset']);
            $response = $this->yellowCardService->getPayments($filters);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getPayments error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment by ID
     * @param string $paymentId
     * @return JsonResponse
     */
    public function getPayment(string $paymentId): JsonResponse
    {
        try {
            $response = $this->yellowCardService->getPayment($paymentId);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getPayment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the estimated network fee for sending the specified stablecoin on the selected network.
     * @param Request $request
     * @return JsonResponse
     */
    public function getNetworkFee(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $token = $request->input('token');
            $response = $this->yellowCardService->getNetworkFee($token);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getNetworkFee error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch network fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a quote for a widget transaction
     * localAmount: Total amount of local currency user is wanting to buy of tokens. This should only be used for BUY transactionType
     * cryptoAmount: Total amount of crypto currency user is wanting to buy or sell. This should only be used for SELL transactionType
     * coin: The type of coin/token that the user is wanting to buy
     * network: The network of the coin/token that the user is wanting to buy
     * ChannelID: The channelId of the payment method that the user is wanting to use. This will be retrieved from the getChannels API
     * transactionType: "Buy" or "Sell"
     * @param Request $request
     * @return JsonResponse
     */
    public function getWidgetQuote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'nullable|string|max:10',
            'localAmount' => 'nullable|integer',
            'cryptoAmount' => 'nullable|decimal:0,18',
            'coin' => 'nullable|string',
            'network' => 'required|string',
            'channelId' => 'required|string',
            'transactionType' => 'nullable|string|in:Buy,Sell',
            'skipValidation' => 'nullable|boolean' // For testing/debugging
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $skipValidation = $request->boolean('skipValidation', false);
            $response = $this->yellowCardService->getWidgetQuote($request->all(), $skipValidation);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard getWidgetQuote error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch widget quote',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate widget quote parameters without calling the API
     * Useful for client-side validation
     * @param Request $request
     * @return JsonResponse
     */
    public function validateWidgetQuote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'nullable|string|max:10',
            'localAmount' => 'nullable|integer',
            'cryptoAmount' => 'nullable|decimal:0,18',
            'channelId' => 'required|string',
            'transactionType' => 'nullable|string|in:Buy,Sell'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validation = $this->yellowCardService->validateWidgetQuoteParams($request->all());
            
            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => $validation['valid'],
                    'error' => $validation['error'],
                    'channel' => $validation['channel']
                ]
            ]);
        } catch (Exception $e) {
            Log::error('YellowCard validateWidgetQuote error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate widget quote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit Payment Request (Payout/Withdrawal)
     * @param Request $request
     * @return JsonResponse
     */
    public function submitPayment(Request $request): JsonResponse
    {
        $customerType = $request->input('customerType', 'retail');
        
        // Base validation rules
        $rules = [
            'channelId' => 'nullable|string',
            'amount' => 'nullable|numeric|required_without:localAmount',
            'localAmount' => 'nullable|numeric|required_without:amount',
            'currencyCode' => 'nullable|string|max:10',
            'reason' => 'required|string',
            'sender' => 'required|array',
            'sender.phone' => 'required|string',
            'destination' => 'required|array',
            'destination.accountNumber' => 'required|string',
            'destination.networkId' => 'required|string',
            'destination.accountType' => 'required|string|in:bank,momo',
            'forceAccept' => 'nullable|boolean',
            'customerType' => 'nullable|string|in:retail,institution'
        ];
        
        // Add conditional validation based on customerType
        if ($customerType === 'retail') {
            $rules = array_merge($rules, [
                'sender.name' => 'required|string',
                'sender.country' => 'required|string|size:2',
                'sender.address' => 'required|string',
                'sender.dob' => 'required|string',
                'sender.email' => 'nullable|email',
                'sender.idNumber' => 'required|string',
                'sender.idType' => 'required|string',
                'sender.additionalIdType' => 'nullable|string',
                'sender.additionalIdNumber' => 'nullable|string'
            ]);
        } elseif ($customerType === 'institution') {
            $rules = array_merge($rules, [
                'sender.businessId' => 'required|string',
                'sender.businessName' => 'required|string'
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get authenticated user
            $user = $request->user();
            
            // Prepare payload with auto-generated fields
            $payload = $request->all();
            $payload['customerUID'] = $user->id ?? $user->uuid ?? uniqid('customer_');
            $payload['customerType'] = $customerType;
            
            $response = $this->yellowCardService->submitPayment($payload);
            
            return response()->json($response, $response['status'] ?? 200);
        } catch (Exception $e) {
            Log::error('YellowCard submitPayment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get collection by ID
     * @param string $collectionId
     * @return JsonResponse
     */
    public function getCollection(string $collectionId): JsonResponse
    {
        try {
            $response = $this->yellowCardService->getCollection($collectionId);
            
            return response()->json($response, $response['status'] ?? 200);
        } catch (Exception $e) {
            Log::error('YellowCard getCollection error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch collection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit Collection Request (Collect payments from customers)
     * This endpoint collects payments FROM customers (deposits/pay-ins)
     * Uses /business/collections endpoint
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCollection(Request $request): JsonResponse
    {
        $customerType = $request->input('customerType', 'retail');
        
        // Base validation rules
        $rules = [
            'channelId' => 'nullable|string',
            'sequenceId' => 'nullable|string',
            'amount' => 'nullable|numeric|required_without:localAmount',
            'localAmount' => 'nullable|numeric|required_without:amount',
            'reason' => 'nullable|string',
            'recipient' => 'required|array',
            'recipient.phone' => 'required|string',
            'source' => 'required|array',
            'source.accountType' => 'nullable|string|in:bank,momo',
            'source.accountNumber' => 'nullable|string',
            'source.networkId' => 'nullable|string',
            'forceAccept' => 'nullable|boolean',
            'customerType' => 'required|string|in:retail,institution',
            'customerUID' => 'nullable|string',
            'redirectUrl' => 'nullable|url'
        ];
        
        // Add conditional validation based on customerType
        if ($customerType === 'retail') {
            $rules = array_merge($rules, [
                'recipient.name' => 'required|string',
                'recipient.country' => 'required|string|size:2',
                'recipient.address' => 'required|string',
                'recipient.dob' => 'required|string',
                'recipient.email' => 'required|email',
                'recipient.idNumber' => 'required|string',
                'recipient.idType' => 'required|string',
                'recipient.additionalIdType' => 'nullable|string',
                'recipient.additionalIdNumber' => 'nullable|string'
            ]);
            
            // Nigeria-specific validation: both NIN and BVN are required
            $recipientCountry = strtoupper($request->input('recipient.country', ''));
            if ($recipientCountry === 'NG') {
                $rules['recipient.additionalIdType'] = 'required|string|in:NIN,BVN';
                $rules['recipient.additionalIdNumber'] = 'required|string';
                $rules['recipient.idType'] = 'required|string|in:NIN,BVN';
            }
        } elseif ($customerType === 'institution') {
            $rules = array_merge($rules, [
                'recipient.businessId' => 'required|string',
                'recipient.businessName' => 'required|string'
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Additional Nigeria validation: ensure idType and additionalIdType are different
        // and that one is NIN and the other is BVN
        if ($recipientCountry === 'NG' && $customerType === 'retail') {
            $idType = $request->input('recipient.idType');
            $additionalIdType = $request->input('recipient.additionalIdType');
            
            if ($idType === $additionalIdType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'recipient.additionalIdType' => ['For Nigeria, ID Type and Additional ID Type must be different (one NIN, one BVN)']
                    ]
                ], 422);
            }
            
            // Ensure we have both NIN and BVN
            $hasNIN = $idType === 'NIN' || $additionalIdType === 'NIN';
            $hasBVN = $idType === 'BVN' || $additionalIdType === 'BVN';
            
            if (!$hasNIN || !$hasBVN) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'recipient.idType' => ['For Nigeria, you must provide both NIN and BVN (one as ID Type, one as Additional ID Type)']
                    ]
                ], 422);
            }
        }

        try {
            // Prepare payload with auto-generated fields
            $payload = $request->all();
            $user = $request->user();
            $payload['customerUID'] = $user->id ?? $user->uuid ?? uniqid('customer_');
            $payload['customerType'] = $customerType;
            $response = $this->yellowCardService->submitCollection($payload);
            
            return response()->json($response, $response['status'] ?? 200);
        } catch (Exception $e) {
            Log::error('YellowCard submitCollection error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit collection request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
