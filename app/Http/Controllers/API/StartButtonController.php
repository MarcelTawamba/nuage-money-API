<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\StartButton\AfricaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StartButtonController extends Controller
{
    protected AfricaService $startButtonService;

    public function __construct(AfricaService $startButtonService)
    {
        $this->startButtonService = $startButtonService;
    }

    /**
     * Get list of banks
     * @param Request $request
     * @return JsonResponse
     */
    public function getBanks(Request $request): JsonResponse
    {
        try {
            $currency = $request->input('currency', 'NGN');
            $type = $request->input('type', 'bank');
            $countryCode = $request->input('countryCode');
            
            $response = $this->startButtonService->getListOfBanks($currency, $type, $countryCode);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton getBanks error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet balance
     * @param Request $request
     * @return JsonResponse
     */
    public function getWalletBalance(Request $request): JsonResponse
    {
        try {
            $response = $this->startButtonService->getWalletBalance();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton getWalletBalance error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wallet balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate bank account
     * @param Request $request
     * @return JsonResponse
     */
    public function validateBankAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bankCode' => 'required|string',
            'accountNumber' => 'required|string',
            'countryCode' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->startButtonService->bankAccountValidation(
                $request->input('bankCode'),
                $request->input('accountNumber'),
                $request->input('countryCode')
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton validateBankAccount error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate bank account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate payment collection
     * @param Request $request
     * @return JsonResponse
     */
    public function requestPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'reference' => 'required|string',
            'currency' => 'required|string',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->startButtonService->requestPayment(
                $request->input('amount'),
                $request->input('reference'),
                $request->input('currency'),
                $request->input('email'),
                $request->input('redirectUrl'),
                $request->input('webhookUrl'),
                $request->input('paymentMethods', []),
                $request->input('metadata', [])
            );
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton requestPayment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to request payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make transfer (payout)
     * @param Request $request
     * @return JsonResponse
     */
    public function makeTransfer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'bankCode' => 'required|string',
            'accountNumber' => 'required|string',
            'reference' => 'required|string',
            'currency' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->startButtonService->makeTransfer($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton makeTransfer error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to make transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check transaction status
     * @param string $reference
     * @return JsonResponse
     */
    public function checkTransaction(string $reference): JsonResponse
    {
        try {
            $response = $this->startButtonService->checkTransaction($reference);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton checkTransaction error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert funds
     * @param Request $request
     * @return JsonResponse
     */
    public function convertFunds(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'from' => 'required|string',
            'to' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->startButtonService->convertFunds($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('StartButton convertFunds error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert funds',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
