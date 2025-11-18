<?php

namespace App\Services\Fincra;

use App\Models\FincraBank;
use App\Models\FincraBankAccount;
use RuntimeException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FincraService
{
    protected string $baseUrl;
    protected  $businessId;
    private const BUSINESS_CACHE_KEY = 'fincra_business_id';
    protected bool $is_production;

    public function __construct()
    {
        $this->baseUrl = env('FINCRA_BASE_URL');

        $this->is_production = false;
        if(strtoupper(env("APP_ENV")) === strtoupper("production")) {
            $this->is_production = true;
        }
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function http() {
        return Http::withHeaders([
            'api-key' => env('FINCRA_API_KEY')
        ]);
    }


    public function getBusinessId() {
        $businessId = null;

        if(strtoupper(env("APP_ENV")) === strtoupper("production")) {
            $businessId = cache()->get(self::BUSINESS_CACHE_KEY);
        }

        if(!$businessId) {
            $endpoint = $this->baseUrl . '/profile/merchants/me';
            $request = $this->http()->get($endpoint);
            Log::channel("slack")->info("getBusinessId Post Response", [
                "theResponse" => $request
            ]);

            if($request->ok() && $request->object()?->success === true) {
                $businessId = $request->object()?->data->business->id;
                if(strtoupper(env("APP_ENV")) === strtoupper("production")) {
                    return cache()->rememberForever(self::BUSINESS_CACHE_KEY, fn () => $businessId);
                }
            }

        }


        return $businessId;
    }

    public function fetchBanks()
    {
        $endpoint = $this->baseUrl . '/core/banks?currency=NGN';
        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException("Could not retreive banks");
        }
    }

    public function resolveAccount(string $accountNumber, string $bankCode)
    {
        $endpoint = $this->baseUrl . '/core/accounts/resolve';

        $requestBody =  [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode
        ];

        $request = $this->http()->post($endpoint, $requestBody);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException("Could not resolve account details");
        }
    }

    public function transferRequestBody($reference, $amount, $description, FincraBankAccount $bankAccount)
    {
        return [
            'sourceCurrency' => 'NGN',
            'destinationCurrency' => 'NGN',
            'amount' => $amount,
            'business' => $this->getBusinessId(),
            'description' => $description,
            'customerReference' => $reference,
            'beneficiary' => [
                'firstName' => 'DEMO',
                'type' => 'individual',
                'accountHolderName' => $bankAccount->account_name,
                'accountNumber' => $bankAccount->account_number,
            ],
            'paymentDestination' => 'bank_account'
        ];
    }

    public function transfer(string $reference, string $accountNumber, string $bankCode, float $amount, FincraBankAccount $bankAccount, $description = "Funds Transfer")
    {
        $endpoint = $this->baseUrl . '/disbursements/payouts';
        $request = $this->http()->post(
            $endpoint,
            $this->transferRequestBody(
                $reference,
                $amount,
                $description,
                $bankAccount
            )
        );

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException("Bank transfer failed to process");
        }
    }

    public function createSubAccount()
    {
        $faker = \Faker\Factory::create();

        $endpoint = "{$this->baseUrl}/profile/business/{$this->getBusinessId()}/sub-accounts";

        $requestBody =  [
            'name' => "{$faker->firstName()} {$faker->lastName()}",
            'email' => "{$faker->email()}",
            'country' => 'NG',
            'mobile' => '09099990099'
        ];

        $request = $this->http()->post($endpoint, $requestBody);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException($request->object()->error);
        }
    }

    public function fetchSubAccounts()
    {
        $endpoint = "{$this->baseUrl}/profile/business/{$this->getBusinessId()}/sub-accounts";

        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException($request->object()->error);
        }
    }

    public function createVirtualAccount(array $payload)
    {

        $endpoint = "{$this->baseUrl}/profile/virtual-accounts/transfer";

        $request = $this->http()->post($endpoint, $payload);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException($request->object()->error);
        }
    }

    /**
     * Generate a quote for currency conversion or payout
     * @param string $sourceCurrency
     * @param string $destinationCurrency
     * @param float $amount
     * @param string $transactionType - 'conversion' or 'disbursement'
     * @param string $paymentDestination - 'fliqpay_wallet', 'bank_account', or 'mobile_money_wallet'
     * @return object|null
     */
    public function generateQuote(
        string $sourceCurrency,
        string $destinationCurrency,
        float $amount,
        string $transactionType = 'conversion',
        string $paymentDestination = 'fliqpay_wallet'
    ) {
        $endpoint = "{$this->baseUrl}/quotes/generate";
        
        $requestBody = [
            'action' => 'send',
            'transactionType' => $transactionType,
            'paymentDestination' => $paymentDestination,
            'sourceCurrency' => strtoupper($sourceCurrency),
            'destinationCurrency' => strtoupper($destinationCurrency),
            'business' => $this->getBusinessId(),
            'amount' => $amount,
            'feeBearer' => 'business',
            'delay' => true
        ];

        $request = $this->http()->post($endpoint, $requestBody);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        }

        return null;
    }

    /**
     * Get real-time FX rates for a currency pair
     * @param string $currencyPair - e.g., 'USD-NGN'
     * @return object|null
     */
    public function getRates(string $currencyPair)
    {
        $endpoint = "{$this->baseUrl}/quotes/treasury-orders/rates?currencyPair={$currencyPair}";
        
        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        }

        return null;
    }

    /**
     * Create a payout/disbursement
     * @param array $payoutData
     * @return object
     */
    public function createPayout(array $payoutData)
    {
        $endpoint = "{$this->baseUrl}/disbursements/payouts";
        
        $requestBody = array_merge([
            'business' => $this->getBusinessId()
        ], $payoutData);

        $request = $this->http()->post($endpoint, $requestBody);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException($request->object()?->message ?? 'Payout failed');
        }
    }

    /**
     * Get wallet balance
     * @return object|null
     */
    public function getWalletBalance()
    {
        $endpoint = "{$this->baseUrl}/profile/business/{$this->getBusinessId()}/wallets";
        
        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        }

        return null;
    }

    /**
     * Get payout status
     * @param string $payoutReference
     * @return object|null
     */
    public function getPayoutStatus(string $payoutReference)
    {
        $endpoint = "{$this->baseUrl}/disbursements/payouts/{$payoutReference}";
        
        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        }

        return null;
    }

    /**
     * Convert currency (move funds between wallets in different currencies)
     * @param string $sourceCurrency
     * @param string $destinationCurrency
     * @param float $amount
     * @param string $quoteReference - From generateQuote()
     * @return object
     */
    public function convertCurrency(
        string $sourceCurrency,
        string $destinationCurrency,
        float $amount,
        string $quoteReference
    ) {
        $endpoint = "{$this->baseUrl}/conversions";
        
        $requestBody = [
            'business' => $this->getBusinessId(),
            'quoteReference' => $quoteReference,
            'sourceCurrency' => strtoupper($sourceCurrency),
            'destinationCurrency' => strtoupper($destinationCurrency),
            'amount' => $amount
        ];

        $request = $this->http()->post($endpoint, $requestBody);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException($request->object()?->message ?? 'Conversion failed');
        }
    }

    /**
     * Create beneficiary for payouts
     * @param array $beneficiaryData
     * @return object
     */
    public function createBeneficiary(array $beneficiaryData)
    {
        $endpoint = "{$this->baseUrl}/profile/beneficiaries/business/{$this->getBusinessId()}";
        
        $request = $this->http()->post($endpoint, $beneficiaryData);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        } else {
            throw new RuntimeException($request->object()?->message ?? 'Failed to create beneficiary');
        }
    }

    /**
     * Get beneficiary by ID
     * @param string $beneficiaryId
     * @return object|null
     */
    public function getBeneficiary(string $beneficiaryId)
    {
        $endpoint = "{$this->baseUrl}/profile/beneficiaries/{$beneficiaryId}";
        
        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        }

        return null;
    }

    /**
     * List all beneficiaries
     * @return array|null
     */
    public function listBeneficiaries()
    {
        $endpoint = "{$this->baseUrl}/profile/beneficiaries/business/{$this->getBusinessId()}";
        
        $request = $this->http()->get($endpoint);

        if ($request->ok() && $request->object()?->success === true) {
            return $request->object()?->data;
        }

        return null;
    }

}
