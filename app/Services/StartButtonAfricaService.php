<?php

namespace App\Services;

use http\Exception\RuntimeException;
use Illuminate\Support\Facades\Http;

class StartButtonAfricaService
{
    private $base_url;

    private $public_key;

    private $secret_key;

    public function __construct()
    {
        $this->base_url = env("STARTBUTTON_ROOT_URL");
        $this->secret_key = env("STARTBUTTON_SECRET_KEY");
        $this->public_key = env("STARTBUTTON_PUBLIC_KEY");
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function http_secret()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.env("STARTBUTTON_SECRET_KEY")
        ]);
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function http_public()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.env("STARTBUTTON_PUBLIC_KEY")
        ]);

    }

    /**
     * @param string $currency
     * @param string $type
     * @return array
     */
    public function getListOfBanks(string $currency="NGN", string $type="bank", string $countryCode = null) {

        $eendpoint = $this->base_url."bank/list/".$currency."?type=".$type;

        if ($countryCode) {
            $eendpoint .= "&countryCode=" . $countryCode;
        }

        $request = $this->http_secret()->get($eendpoint);

        return $this->requestTreatment($request);
    }

    /**
     * @param float $amount: This should be in fractional units. kobo for NGN, pesewas for GHS. 300 will be passed as 30000
     * @param string $reference: a unique identifier for our collection transaction
     * @param string $currency
     * @param string $email: email address of payer
     * @return array
     */
    public function requestPayment(
        float $amount, string $reference="",
        string $currency="NGN", string $email="mtawamba@nuage.money",
        string $redirectUrl = null, string $webhookUrl = null,
        array $paymentMethods = [], array $metadata = []
    ){
        //$amount = $amount * 100;
        $eendpoint = $this->base_url."transaction/initialize";
        $postingData = [
            'email' => $email,
            'reference' => $reference,
            'currency' => $currency,
            'amount' => $amount
        ];

        if ($redirectUrl) {
            $postingData['redirectUrl'] = $redirectUrl;
        }

        if ($webhookUrl) {
            $postingData['webhookUrl'] = $webhookUrl;
        }

        if (!empty($paymentMethods)) {
            $postingData['paymentMethods'] = $paymentMethods;
        }

        if (!empty($metadata)) {
            $postingData['metadata'] = $metadata;
        }

        $request = $this->http_public()->post($eendpoint, $postingData);

        return $this->requestTreatment($request);
    }


    /**
     * @param string $bankCode
     * @param string $accountNumber
     * @return array
     */
    public function bankAccountValidation(string $bankCode, string $accountNumber) {
        $eendpoint = $this->base_url."bank/verify?bankCode=".$bankCode."&accountNumber=".$accountNumber;

        $request = $this->http_secret()->get($eendpoint);

        return $this->requestTreatment($request);
    }

    /**
     * @param float $amount
     * @param string $bankCode
     * @param string $accountNumber
     * @param string $reference
     * @param $currency
     * @return array
     */
    public function makeTransfer(array $data)
    {
        $data['amount'] = $data['amount'] * 100;
        $endpoint = $this->base_url . "transaction/transfer";
        $request = $this->http_secret()->post($endpoint, $data);
        return $this->requestTreatment($request);
    }

    /**
     * @param string $reference
     * @return array
     */
    public function checkTransaction(string $reference)
    {
        $endpoint = $this->base_url."transaction/status/".$reference;

        $request = $this->http_secret()->get($endpoint);

        return $this->requestTreatment($request);
    }


    /**
     * @param \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response $request
     * @return array
     */
    private function
    requestTreatment(\GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response $request): array
    {
        if ($request->ok() && $request->object()?->success === true) {
            return [
                "success" => true,
                "data" => $request->object()->data
            ];
        } else {
            return [
                "success" => false,
                "data" => $request->object()->message
            ];
        }
    }
}
