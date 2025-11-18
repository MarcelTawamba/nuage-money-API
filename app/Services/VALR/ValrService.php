<?php

namespace App\Services\VALR;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValrService
{
    private $base_url;

    private $api_key;

    private $api_secret;

    public function __construct()
    {
        $this->base_url = env("VALR_BASE_URL", "https://api.valr.com");
        $this->api_key = env("VALR_API_KEY");
        $this->api_secret = env("VALR_API_SECRET");
    }

    /**
     * Generate VALR API signature
     * @param int $timestamp
     * @param string $verb - HTTP method (GET, POST, DELETE, etc.)
     * @param string $path - API path without base URL
     * @param string $body - Request body (empty string for GET requests)
     * @param string $subaccountId - Optional subaccount ID for impersonation
     * @return string
     */
    private function generateSignature(int $timestamp, string $verb, string $path, string $body = "", string $subaccountId = null)
    {
        $payload = $timestamp . strtoupper($verb) . $path . $body;
        if ($subaccountId) {
            $payload .= $subaccountId;
        }
        return hash_hmac('sha512', $payload, $this->api_secret);
    }

    /**
     * @param string $verb - HTTP method
     * @param string $path - API path without base URL
     * @param array $body - Request body data
     * @param string $subaccountId - Optional subaccount ID for impersonation
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function http(string $verb, string $path, array $body = [], string $subaccountId = null)
    {
        $timestamp = intval(microtime(true) * 1000);
        $bodyString = empty($body) ? "" : json_encode($body);
        $signature = $this->generateSignature($timestamp, $verb, $path, $bodyString, $subaccountId);

        $headers = [
            'Content-Type' => 'application/json',
            'X-VALR-API-KEY' => $this->api_key,
            'X-VALR-SIGNATURE' => $signature,
            'X-VALR-TIMESTAMP' => $timestamp
        ];

        if ($subaccountId) {
            $headers['X-VALR-SUBACCOUNT-ID'] = $subaccountId;
        }

        return Http::withHeaders($headers);
    }

    /**
     * Get account balances
     * @return array
     */
    public function getBalances()
    {
        $path = "/v1/account/balances";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get list of all supported markets/trading pairs
     * @return array
     */
    public function getMarkets()
    {
        $path = "/v1/public/markets";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get market data for all pairs
     * @return array
     */
    public function getMarketData()
    {
        $path = "/v1/marketdata";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Place a limit order
     * @param string $side - BUY or SELL
     * @param float $quantity
     * @param float $price
     * @param string $pair - Trading pair e.g., BTCZAR
     * @param string $postOnly - true or false
     * @param string $customerOrderId - Optional custom order ID
     * @return array
     */
    public function placeLimitOrder(
        string $side,
        float $quantity,
        float $price,
        string $pair,
        string $postOnly = "false",
        string $customerOrderId = null
    ) {
        $path = "/v1/orders/limit";
        $data = [
            'side' => $side,
            'quantity' => $quantity,
            'price' => $price,
            'pair' => $pair,
            'postOnly' => $postOnly
        ];

        if ($customerOrderId) {
            $data['customerOrderId'] = $customerOrderId;
        }

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
    }

    /**
     * Place a market order
     * @param string $side - BUY or SELL
     * @param float $baseAmount - Amount in base currency (for BUY)
     * @param float $quoteAmount - Amount in quote currency (for SELL)
     * @param string $pair - Trading pair e.g., BTCZAR
     * @param string $customerOrderId - Optional custom order ID
     * @return array
     */
    public function placeMarketOrder(
        string $side,
        string $pair,
        float $baseAmount = null,
        float $quoteAmount = null,
        string $customerOrderId = null
    ) {
        $path = "/v1/orders/market";
        $data = [
            'side' => $side,
            'pair' => $pair
        ];

        if ($baseAmount) {
            $data['baseAmount'] = $baseAmount;
        }

        if ($quoteAmount) {
            $data['quoteAmount'] = $quoteAmount;
        }

        if ($customerOrderId) {
            $data['customerOrderId'] = $customerOrderId;
        }

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
    }

    /**
     * Get order status by order ID
     * @param string $orderId
     * @return array
     */
    public function getOrderStatus(string $orderId)
    {
        $path = "/v1/orders/{$orderId}";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get all open orders
     * @return array
     */
    public function getOpenOrders()
    {
        $path = "/v1/orders/open";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Cancel an order
     * @param string $orderId
     * @return array
     */
    public function cancelOrder(string $orderId)
    {
        $path = "/v1/orders/order";
        $data = ['orderId' => $orderId];
        return $this->http('DELETE', $path, $data)->delete($this->base_url . $path, $data);
    }

    /**
     * Get trade history
     * @param string $pair - Optional trading pair filter
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getTradeHistory(string $pair = null, int $limit = 100)
    {
        $path = "/v1/account/trades";
        $params = ['limit' => $limit];
        
        if ($pair) {
            $params['pair'] = $pair;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;
        
        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Get deposit history
     * @param string $currency - Optional currency filter
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getDepositHistory(string $currency = null, int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/deposits/history";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        if ($currency) {
            $params['currency'] = $currency;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Get withdrawal history
     * @param string $currency - Optional currency filter
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getWithdrawalHistory(string $currency = null, int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/withdrawals/history";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        if ($currency) {
            $params['currency'] = $currency;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Initiate a cryptocurrency withdrawal
     * @param string $currency
     * @param float $amount
     * @param string $address - Withdrawal address
     * @return array
     */
    public function withdraw(string $currency, float $amount, string $address)
    {
        $path = "/v1/account/withdraw";
        $data = [
            'currency' => $currency,
            'amount' => $amount,
            'address' => $address
        ];

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
    }

    /**
     * Get withdrawal status
     * @param string $withdrawalId
     * @return array
     */
    public function getWithdrawalStatus(string $withdrawalId)
    {
        $path = "/v1/account/withdrawals/{$withdrawalId}";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get deposit address for a currency
     * @param string $currency
     * @return array
     */
    public function getDepositAddress(string $currency)
    {
        $path = "/v1/account/deposit/address/{$currency}";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get transaction history
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getTransactionHistory(int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/transactionhistory";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Get market summary for all pairs
     * @return array
     */
    public function getMarketSummary()
    {
        $path = "/v1/marketsummary";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get order book for a currency pair
     * @param string $currencyPair - e.g., BTCZAR
     * @return array
     */
    public function getOrderBook(string $currencyPair)
    {
        $path = "/v1/public/{$currencyPair}/orderbook";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }

    /**
     * Get recent trades for a currency pair
     * @param string $currencyPair - e.g., BTCZAR
     * @param int $limit - Number of trades to return
     * @return array
     */
    public function getPublicTrades(string $currencyPair, int $limit = 100)
    {
        $path = "/v1/public/{$currencyPair}/trades";
        $params = ['limit' => $limit];
        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Get simple quote for buying or selling crypto
     * @param string $currencyPair - e.g., BTCZAR
     * @param string $payInCurrency - Currency to pay in
     * @param float $payAmount - Amount to pay
     * @param string $side - BUY or SELL
     * @return array
     */
    public function getSimpleQuote(string $currencyPair, string $payInCurrency, float $payAmount, string $side)
    {
        $path = "/v1/simple/{$currencyPair}/quote";
        $params = [
            'payInCurrency' => $payInCurrency,
            'payAmount' => $payAmount,
            'side' => $side
        ];
        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Execute simple buy/sell order
     * @param string $currencyPair - e.g., BTCZAR
     * @param string $payInCurrency - Currency to pay in
     * @param float $payAmount - Amount to pay
     * @param string $side - BUY or SELL
     * @return array
     */
    public function placeSimpleOrder(string $currencyPair, string $payInCurrency, float $payAmount, string $side)
    {
        $path = "/v1/simple/{$currencyPair}/order";
        $data = [
            'payInCurrency' => $payInCurrency,
            'payAmount' => $payAmount,
            'side' => $side
        ];

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
    }

    /**
     * Get order history summary
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getOrderHistory(int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/orderhistory";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];
        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
    }

    /**
     * Get detailed order history with fills
     * @param string $orderId
     * @return array
     */
    public function getOrderHistoryDetail(string $orderId)
    {
        $path = "/v1/account/orderhistory/detail/{$orderId}";
        return $this->http('GET', $path)->get($this->base_url . $path);
    }
}
