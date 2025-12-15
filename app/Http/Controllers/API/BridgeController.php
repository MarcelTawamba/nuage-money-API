<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Bridge\BridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BridgeController extends Controller
{
  protected BridgeService $bridgeService;

  public function __construct(BridgeService $bridgeService)
  {
    $this->bridgeService = $bridgeService;
  }

  /**
   * Get Total Balances across all Bridge Wallets
   * @param Request $request
   * @return JsonResponse
   */
  public function getTotalBalances(Request $request): JsonResponse {
    try {
      $response = $this->bridgeService->getTotalBalances();

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE getTotalBalances error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch total balances',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get Bridge Wallets with pagination
   * @param Request $request
   * @return JsonResponse
   */
  public function getWallets(Request $request): JsonResponse {
    try {
      // Validate the request parameters
      $validator = Validator::make($request->all(), [
        'limit' => 'integer|min:10|max:100',
        'starting_after' => 'nullable|string',
        'ending_before' => 'nullable|string'
      ]);

      if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
      }

      $limit = $request->input('limit', 10);
      $starting_after = $request->input('starting_after');
      $ending_before = $request->input('ending_before');

      $response = $this->bridgeService->getWallets($limit, $starting_after, $ending_before);

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE getWallets error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch wallets',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get wallet transaction history
   * @param Request $request
   * @param string $walletId - Wallet ID from URL path
   * @return JsonResponse
   */
  public function getWalletTransactionHistory(Request $request, string $walletId): JsonResponse 
  {
    try {
      // Validate only query parameters (walletId comes from URL path)
      $validator = Validator::make($request->all(), [
        'limit' => 'integer|min:10|max:100',
        'starting_after' => 'nullable|string',
        'ending_before' => 'nullable|string'
      ]);
      
      if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
      }
      
      $limit = $request->input('limit', 10);
      $starting_after = $request->input('starting_after');
      $ending_before = $request->input('ending_before');

      $response = $this->bridgeService->getWalletTransactionHistory($walletId, $limit, $starting_after, $ending_before);
      return response()->json([
        'success' => true,
        'data' => $response
      ]);

    } catch (\Exception $e) {
      Log::error('BRIDGE getWalletTransactionHistory error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch wallet transaction history',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get all customers
   * @return JsonResponse
   */
  public function getCustomers(): JsonResponse {
    try {
      $response = $this->bridgeService->getAllCustomers();

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE getAllCustomers error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch all customers',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get wallets by customer ID
   * @param Request $request
   * @param string $customerId - Customer ID from URL path
   * @return JsonResponse
   */
  public function getCustomerWallets(Request $request, string $customerId): JsonResponse {
    try {
      $response = $this->bridgeService->getCustomerWallets($customerId);

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE getWalletsByCustomerId error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch wallets for customer ID: ' . $customerId,
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Create a wallet for a customer
   * @param Request $request
   * @param string $customerId - Customer ID from URL path
   * @return JsonResponse
   */
  public function createWallet(Request $request, string $customerId): JsonResponse {
    try {
      // Validate the request parameters
      $validator = Validator::make($request->all(), [
        'chain' => 'required|string|in:base,ethereum,solana',
        'idempotencyKey' => 'nullable|string'
      ]);

      if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
      }

      $chain = $request->input('chain', 'ethereum');
      $idempotencyKey = $request->input('idempotencyKey');

      $response = $this->bridgeService->createWallet($customerId, $chain, $idempotencyKey);

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE createWallet error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to create wallet for customer ID: ' . $customerId,
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get exchange rates between currencies
   * @param Request $request
   * @return JsonResponse
   */
  public function getExchangeRates(Request $request): JsonResponse {
    try {
      // Define allowed currency pairs
      $allowedPairs = [
        'usd-eur',
        'usd-mxn',
        'usd-brl',
        'brl-usd',
        'btc-usd',
        'eth-usd',
        'eur-usd',
        'mxn-usd',
        'sol-usd',
        'usdt-usd'
      ];

      // Validate the request parameters
      $validator = Validator::make($request->all(), [
        'from' => 'required|string|in:brl,btc,eth,eur,mxn,sol,usd,usdt',
        'to' => 'required|string|in:brl,btc,eth,eur,mxn,sol,usd,usdt'
      ]);

      if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
      }

      $from = $request->input('from');
      $to = $request->input('to');

      // Validate currency pair combination
      $currencyPair = strtolower($from) . '-' . strtolower($to);
      if (!in_array($currencyPair, $allowedPairs)) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => [
              'currency_pair' => [
                "The currency pair {$from}-{$to} is not supported. Supported pairs are: " . implode(', ', array_map('strtoupper', $allowedPairs))
              ]
            ]
        ], 422);
      }

      $response = $this->bridgeService->getExchangeRates($from, $to);

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE getExchangeRates error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch exchange rates',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Create a transfer
   * @param Request $request
   * @return JsonResponse
   */
  public function createTransfer(Request $request): JsonResponse {
    try {
      // Validate the request parameters
      $validator = Validator::make($request->all(), [
        'on_behalf_of' => 'required|string|min:1|max:42',
        'amount' => 'nullable|string',
        'client_reference_id' => 'nullable|string',
        'developer_fee' => 'nullable|string',
        'developer_fee_percent' => 'nullable|string',
        'dry_run' => 'nullable|boolean',
        'idempotencyKey' => 'nullable|string',
        
        // Source validations
        'source' => 'required|array',
        'source.currency' => 'required|string|in:dai,eur,eurc,mxn,pyusd,usd,usdb,usdc,usdt',
        'source.payment_rail' => 'required|string|in:ach,wire,ach_push,ach_same_day,arbitrum,avalanche_c_chain,base,bridge_wallet,ethereum,optimism,polygon,sepa,solana,spei,stellar,swift,tron',
        'source.payment_scheme' => 'nullable|string|in:reversed_payment,sepa_credit,sepa_instant',
        'source.external_account_id' => 'nullable|string|min:1|max:42',
        'source.bridge_wallet_id' => 'nullable|string|min:1|max:42',
        'source.from_address' => 'nullable|string',
        
        // Destination validations
        'destination' => 'required|array',
        'destination.currency' => 'required|string|in:dai,eur,eurc,mxn,pyusd,usd,usdb,usdc,usdt',
        'destination.payment_rail' => 'required|string|in:ach,wire,ach_push,ach_same_day,arbitrum,avalanche_c_chain,base,ethereum,fiat_deposit_return,optimism,polygon,sepa,solana,spei,stellar,swift,tron',
        'destination.external_account_id' => 'nullable|string|min:1|max:42',
        'destination.bridge_wallet_id' => 'nullable|string|min:1|max:42',
        'destination.wire_message' => 'nullable|string|min:1|max:256',
        'destination.sepa_reference' => 'nullable|string|min:6|max:140',
        'destination.swift_reference' => 'nullable|string|min:1|max:190',
        'destination.spei_reference' => 'nullable|string|min:1|max:40',
        'destination.reference' => 'nullable|string',
        'destination.swift_charges' => 'nullable|string|in:our,sha',
        'destination.ach_reference' => 'nullable|string|min:1|max:10',
        'destination.blockchain_memo' => 'nullable|string',
        'destination.deposit_id' => 'nullable|string|min:1|max:42',
        'destination.to_address' => 'nullable|string',
        
        // Features validations
        'features' => 'nullable|array',
        'features.flexible_amount' => 'nullable|boolean',
        'features.static_template' => 'nullable|boolean',
        'features.allow_any_from_address' => 'nullable|boolean',
      ]);

      if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
      }

      $source = $request->input('source');
      $destination = $request->input('destination');

      // Custom validation: source.currency = 'eur' requires source.payment_rail = 'sepa'
      if (isset($source['currency']) && strtolower($source['currency']) === 'eur') {
        if (!isset($source['payment_rail']) || strtolower($source['payment_rail']) !== 'sepa') {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'source.payment_rail' => ['When source.currency is EUR, source.payment_rail must be SEPA.']
              ]
          ], 422);
        }
        
        // When source.currency = 'eur', only 'usdc' and 'eurc' are supported as destination.currency
        if (isset($destination['currency']) && !in_array(strtolower($destination['currency']), ['usdc', 'eurc'])) {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'destination.currency' => ['When source.currency is EUR, only USDC and EURC are supported as destination.currency.']
              ]
          ], 422);
        }
      }

      // Custom validation: source.payment_rail = 'sepa' requires source.currency = 'eur'
      if (isset($source['payment_rail']) && strtolower($source['payment_rail']) === 'sepa') {
        if (!isset($source['currency']) || strtolower($source['currency']) !== 'eur') {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'source.currency' => ['When source.payment_rail is SEPA, source.currency must be EUR.']
              ]
          ], 422);
        }
        
        // When source.payment_rail = 'sepa', only 'usdc' or 'eurc' are supported as destination currency
        if (isset($destination['currency']) && !in_array(strtolower($destination['currency']), ['usdc', 'eurc'])) {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'destination.currency' => ['When source.payment_rail is SEPA, only USDC or EURC are supported as destination.currency.']
              ]
          ], 422);
        }
      }

      // Custom validation: destination.currency = 'eur' requires destination.payment_rail = 'sepa'
      if (isset($destination['currency']) && strtolower($destination['currency']) === 'eur') {
        if (!isset($destination['payment_rail']) || strtolower($destination['payment_rail']) !== 'sepa') {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'destination.payment_rail' => ['When destination.currency is EUR, destination.payment_rail must be SEPA.']
              ]
          ], 422);
        }
      }

      // Custom validation: destination.payment_rail = 'sepa' requires destination.currency = 'eur'
      if (isset($destination['payment_rail']) && strtolower($destination['payment_rail']) === 'sepa') {
        if (!isset($destination['currency']) || strtolower($destination['currency']) !== 'eur') {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'destination.currency' => ['When destination.payment_rail is SEPA, destination.currency must be EUR.']
              ]
          ], 422);
        }
      }

      // Custom validation: destination.payment_rail = 'swift' requires destination.currency = 'usd'
      if (isset($destination['payment_rail']) && strtolower($destination['payment_rail']) === 'swift') {
        if (!isset($destination['currency']) || strtolower($destination['currency']) !== 'usd') {
          return response()->json([
              'success' => false,
              'message' => 'Validation error',
              'errors' => [
                'destination.currency' => ['When destination.payment_rail is SWIFT, destination.currency must be USD.']
              ]
          ], 422);
        }
      }

      $response = $this->bridgeService->createTransfer(
        $request->input('on_behalf_of'),
        $source,
        $destination,
        $request->input('amount'),
        $request->input('client_reference_id'),
        $request->input('developer_fee'),
        $request->input('developer_fee_percent'),
        $request->input('dry_run'),
        $request->input('features'),
        $request->input('idempotencyKey')
      );

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE createTransfer error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to create transfer',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get transfer details by ID
   * @param Request $request
   * @param string $transferId - Transfer ID from URL path
   * @return JsonResponse
   */
  public function getTransfer(Request $request, string $transferId): JsonResponse {
    try {
      $response = $this->bridgeService->getTransfer($transferId);

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE getTransfer error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch transfer details',
          'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * List all transfers with pagination
   * @param Request $request
   * @return JsonResponse
   */
  public function listTransfers(Request $request): JsonResponse {
    try {
      // Validate the request parameters
      $validator = Validator::make($request->all(), [
        'limit' => 'integer|min:10|max:100',
        'starting_after' => 'nullable|string',
        'ending_before' => 'nullable|string'
      ]);

      if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
      }

      $limit = $request->input('limit', 10);
      $starting_after = $request->input('starting_after');
      $ending_before = $request->input('ending_before');

      $response = $this->bridgeService->listTransfers($limit, $starting_after, $ending_before);

      return response()->json([
        'success' => true,
        'data' => $response
      ]);
    } catch (\Exception $e) {
      Log::error('BRIDGE listTransfers error: ' . $e->getMessage());
      
      return response()->json([
          'success' => false,
          'message' => 'Failed to fetch transfers list',
          'error' => $e->getMessage()
      ], 500);
    }
  }
}