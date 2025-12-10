<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\VALR\ValrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ValrController extends Controller
{
    protected ValrService $valrService;

    public function __construct(ValrService $valrService)
    {
        $this->valrService = $valrService;
    }

    /**
     * Get account balances
     * @param Request $request
     * @return JsonResponse
     */
    public function getBalances(Request $request): JsonResponse
    {
        try {
            // Get optional currencies filter from query params or request body
            $currencies = $request->input('currencies', []);
            
            // Ensure currencies is an array
            if (!is_array($currencies)) {
                $currencies = [];
            }
            
            $response = $this->valrService->getBalances($currencies);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getBalances error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch balances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Place a limit order
     * @param Request $request
     * @return JsonResponse
     */
    public function placeLimitOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'side' => 'required|in:BUY,SELL',
            'quantity' => 'required|numeric|min:0',
            'pair' => 'required|string',
            'price' => 'nullable|string',
            'postOnly' => 'nullable|in:true,false',
            'customerOrderId' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Determine price: use provided price or fetch from market
            if ($request->has('price') && $request->price !== null) {
                // User provided a custom price
                $price = $request->price;
                Log::info('VALR placeLimitOrder - Using custom price', [
                    'pair' => $request->pair,
                    'price' => $price
                ]);
            } else {
                // Price not provided, fetch the last traded price from market summary
                $marketSummary = $this->valrService->getMarketSummaryForCurrencyPair($request->pair);
                
                if (empty($marketSummary) || !isset($marketSummary['lastTradedPrice'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch market price for the currency pair',
                        'error' => 'Unable to determine lastTradedPrice'
                    ], 400);
                }
                
                $price = $marketSummary['lastTradedPrice'];
                Log::info('VALR placeLimitOrder - Using market lastTradedPrice', [
                    'pair' => $request->pair,
                    'lastTradedPrice' => $price
                ]);
            }
            
            $response = $this->valrService->placeLimitOrder(
                $request->side,
                $request->quantity,
                $price,
                $request->pair,
                $request->postOnly ?? 'false',
                $request->customerOrderId
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR placeLimitOrder error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place limit order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Place a market order
     * @param Request $request
     * @return JsonResponse
     */
    public function placeMarketOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'side' => 'required|in:BUY,SELL',
            'pair' => 'required|string',
            'baseAmount' => 'nullable|numeric|min:0',
            'quoteAmount' => 'nullable|numeric|min:0',
            'customerOrderId' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->valrService->placeMarketOrder(
                $request->side,
                $request->pair,
                $request->baseAmount,
                $request->quoteAmount,
                $request->customerOrderId
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR placeMarketOrder error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place market order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order status by order ID
     * @param string $orderId
     * @return JsonResponse
     */
    public function getOrderStatus(string $currencyPair, string $orderId): JsonResponse
    {
        try {
            $response = $this->valrService->getOrderStatus($currencyPair, $orderId);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getOrderStatus error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all open orders
     * @return JsonResponse
     */
    public function getOpenOrders(): JsonResponse
    {
        try {
            $response = $this->valrService->getOpenOrders();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getOpenOrders error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch open orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an order
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orderId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->valrService->cancelOrder($request->orderId);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR cancelOrder error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get simple quote for buying or selling crypto
     * @param Request $request
     * @return JsonResponse
     */
    public function getSimpleQuote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currencyPair' => 'required|string',
            'payInCurrency' => 'required|string',
            'payAmount' => 'required|numeric|min:0',
            'side' => 'required|in:BUY,SELL'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->valrService->getSimpleQuote(
                $request->currencyPair,
                $request->payInCurrency,
                $request->payAmount,
                $request->side
            );
            
            // Check if response is null or empty (404 error)
            if (empty($response)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Simple quote API not available. This endpoint may not be supported by VALR or requires special access.',
                    'suggestion' => 'Consider using regular limit/market orders instead.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getSimpleQuote error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get quote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute simple buy/sell order
     * @param Request $request
     * @return JsonResponse
     */
    public function placeSimpleOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currencyPair' => 'required|string',
            'payInCurrency' => 'required|string',
            'payAmount' => 'required|numeric|min:0',
            'side' => 'required|in:BUY,SELL'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->valrService->placeSimpleOrder(
                $request->currencyPair,
                $request->payInCurrency,
                $request->payAmount,
                $request->side
            );
            
            // Check if response is null or empty (404 error)
            if (empty($response)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Simple order API not available. This endpoint may not be supported by VALR or requires special access.',
                    'suggestion' => 'Consider using regular limit/market orders instead.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR placeSimpleOrder error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place simple order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deposit address for a currency
     * @param string $currency
     * @return JsonResponse
     */
    public function getDepositAddress(string $currency): JsonResponse
    {
        try {
            $response = $this->valrService->getDepositAddress($currency);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getDepositAddress error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch deposit address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get market summary for all pairs
     * @return JsonResponse
     */
    public function getMarketSummary(): JsonResponse
    {
        try {
            $response = $this->valrService->getMarketSummary();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getMarketSummary error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch market summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get market summary for a currency pair
     * @return JsonResponse
     */
    public function getMarketSummaryForCurrencyPair(string $currencyPair): JsonResponse
    {
        try {
            $response = $this->valrService->getMarketSummaryForCurrencyPair($currencyPair);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getMarketSummaryForCurrencyPair error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch market summary for currency pair',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Currency Pairs
     * @return JsonResponse
     */
    public function getCurrencyPairs(): JsonResponse
    {
        try {
            $response = $this->valrService->getCurrencyPairs();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCurrencyPairs error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currency pairs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order book for a currency pair
     * @param string $currencyPair
     * @return JsonResponse
     */
    public function getOrderBook(string $currencyPair): JsonResponse
    {
        try {
            $response = $this->valrService->getOrderBook($currencyPair);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getOrderBook error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a payment using VALR Pay
     * @param Request $request
     * @return JsonResponse
     */
    public function makePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'recipientEmail' => 'nullable|email',
            'recipientCellNumber' => 'nullable|string',
            'recipientPayId' => 'nullable|string',
            'recipientNote' => 'nullable|string',
            'senderNote' => 'nullable|string',
            'anonymous' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that at least one recipient identifier is provided
        if (!$request->recipientEmail && !$request->recipientCellNumber && !$request->recipientPayId) {
            return response()->json([
                'success' => false,
                'message' => 'At least one recipient identifier is required (recipientEmail, recipientCellNumber, or recipientPayId)'
            ], 422);
        }

        try {
            $response = $this->valrService->makePayment(
                $request->amount,
                $request->currency,
                $request->recipientEmail,
                $request->recipientCellNumber,
                $request->recipientPayId,
                $request->recipientNote,
                $request->senderNote,
                $request->anonymous ?? false
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR makePayment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to make payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get linked bank accounts for a currency
     * @param string $currencyCode
     * @return JsonResponse
     */
    public function getLinkedBankAccounts(string $currencyCode): JsonResponse
    {
        try {
            $response = $this->valrService->getLinkedBankAccounts($currencyCode);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getLinkedBankAccounts error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch linked bank accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a fiat withdrawal to a linked bank account
     * @param Request $request
     * @param string $currencyCode
     * @return JsonResponse
     */
    public function makeFiatWithdrawal(Request $request, string $currencyCode): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => 'required|string',
                'linkedBankAccountId' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $response = $this->valrService->makeFiatWithdrawal(
                $currencyCode,
                $request->amount,
                $request->linkedBankAccountId
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR makeFiatWithdrawal error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to make fiat withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get crypto wallet deposit address for a currency
     * @param string $currencyCode
     * @return JsonResponse
     */
    public function getCryptoCurrencyWalletAddress(string $currencyCode): JsonResponse
    {
        try {
            $response = $this->valrService->getCryptoCurrencyWalletAddress($currencyCode);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCryptoCurrencyWalletAddress error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch crypto wallet address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a crypto withdrawal
     * @param Request $request
     * @param string $currencyCode
     * @return JsonResponse
     */
    public function makeCryptoWithdrawal(Request $request, string $currencyCode): JsonResponse
    {
        try {
            // Validate request - either address OR addressBookId (not both), and either serviceProviderName OR serviceProviderId (not both)
            $validator = Validator::make($request->all(), [
                'amount' => 'required|string',
                'address' => 'nullable|string',
                'addressBookId' => 'nullable|string',
                'beneficiaryName' => 'required|string',
                'isCorporate' => 'required|boolean',
                'isSelfHosted' => 'required|boolean',
                'serviceProviderName' => 'nullable|string',
                'serviceProviderId' => 'nullable|string',
                'networkType' => 'nullable|string',
                'allowBorrow' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate that only one of address or addressBookId is provided
            if ($request->has('address') && $request->has('addressBookId')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'address' => ['Cannot provide both address and addressBookId. Use one or the other.']
                    ]
                ], 422);
            }

            if (!$request->has('address') && !$request->has('addressBookId')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'address' => ['Must provide either address or addressBookId.']
                    ]
                ], 422);
            }

            // Validate that only one of serviceProviderName or serviceProviderId is provided
            if ($request->has('serviceProviderName') && $request->has('serviceProviderId')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'serviceProvider' => ['Cannot provide both serviceProviderName and serviceProviderId. Use one or the other.']
                    ]
                ], 422);
            }

            if (!$request->has('serviceProviderName') && !$request->has('serviceProviderId')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'serviceProvider' => ['Must provide either serviceProviderName or serviceProviderId.']
                    ]
                ], 422);
            }

            $response = $this->valrService->makeCryptoWithdrawal(
                $currencyCode,
                $request->amount,
                $request->input('address'),
                $request->input('addressBookId'),
                $request->beneficiaryName,
                $request->isCorporate,
                $request->isSelfHosted,
                $request->input('serviceProviderName'),
                $request->input('serviceProviderId'),
                $request->networkType,
                $request->allowBorrow
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR makeCryptoWithdrawal error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to make crypto withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of crypto service providers
     * @return JsonResponse
     */
    public function getCryptoServiceProviders(): JsonResponse
    {
        try {
            $response = $this->valrService->getCryptoServiceProviders();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCryptoServiceProviders error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch crypto service providers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of currencies supported by VALR
     * @return JsonResponse
     */
    public function getCurrencies(): JsonResponse
    {
        try {
            $response = $this->valrService->getCurrencies();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCurrencies error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currencies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Withdrawal config Info
     * @return JsonResponse
     */
    public function getWithdrawalConfigInfo(string $currencyCode): JsonResponse
    {
        try {
            $response = $this->valrService->getWithdrawalConfigInfoForCryptoCurrency($currencyCode);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getWithdrawalConfigInfo error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch withdrawal config info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get crypto withdrawal history
     * @param Request $request
     * @return JsonResponse
     */
    public function getCryptoWithdrawalHistory(Request $request): JsonResponse
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'skip' => 'integer|min:0',
                'limit' => 'integer|min:1|max:200',
                'currency' => 'string',
                'startTime' => 'date_format:Y-m-d\TH:i:s\Z',
                'endTime' => 'date_format:Y-m-d\TH:i:s\Z'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $skip = $request->input('skip', 0);
            $limit = $request->input('limit', 10);
            $currency = $request->input('currency');
            $startTime = $request->input('startTime');
            $endTime = $request->input('endTime');

            $response = $this->valrService->getCryptoWithdrawalHistory(
                $skip,
                $limit,
                $currency,
                $startTime,
                $endTime
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCryptoWithdrawalHistory error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch crypto withdrawal history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get crypto withdrawal status
     * @param string $currencyCode
     * @param string $withdrawId
     * @return JsonResponse
     */
    public function getCryptoWithdrawalStatus(string $currencyCode, string $withdrawId): JsonResponse
    {
        try {
            $response = $this->valrService->getCryptoWithdrawalStatus($currencyCode, $withdrawId);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCryptoWithdrawalStatus error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch crypto withdrawal status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get whitelisted crypto withdrawal address book
     * @return JsonResponse
     */
    public function getCryptoAddressBook(): JsonResponse
    {
        try {
            $response = $this->valrService->getCryptoAddressBook();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('VALR getCryptoAddressBook error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch crypto address book',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate if an address is whitelisted
     * @param Request $request
     * @return JsonResponse
     */
    public function validateWhitelistedAddress(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required|string',
                'currencyCode' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $address = $request->input('address');
            $currencyCode = $request->input('currencyCode');

            $result = $this->valrService->validateWhitelistedAddress($address, $currencyCode);
            
            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address is not whitelisted',
                    'data' => [
                        'address' => $address,
                        'currency' => $currencyCode,
                        'isWhitelisted' => false
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Address is whitelisted',
                'data' => [
                    'address' => $address,
                    'currency' => $currencyCode,
                    'isWhitelisted' => true,
                    'addressBookEntry' => $result
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('VALR validateWhitelistedAddress error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate whitelisted address',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
