<?php

namespace App\Classes;

use App\Models\ExchangeRateMargin;
use GuzzleHttp\Client;

class ConvertionHelper
{
    /**
     * @param float $amountFrom
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param float $percentageCom
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function convertWithFrom(float $amountFrom, string $currencyFrom, string $currencyTo, float $percentageCom = 0)
    {
        $amount_remove = $amountFrom * ($percentageCom/100);
        $amount_to_work_with = $amountFrom - $amount_remove;

        if ((strtoupper($currencyFrom)== "XAF" && strtoupper($currencyTo)== "NGN") || (strtoupper($currencyFrom)== "NGN" && strtoupper($currencyTo)== "XAF")  ){
            $margin1 = ExchangeRateMargin::where("from_currency",$currencyFrom)->where("to_currency","USD")->first();
            $margin2 = ExchangeRateMargin::where("from_currency","USD")->where("to_currency",$currencyTo)->first();


            $amountTo =$amount_to_work_with * $margin1->rate;

            $amountTo = $amountTo * $margin2->rate;

            return [
                "amountFrom" => $amountFrom,
                "amountTo" => $amountTo,
                "currencyFrom" => $currencyFrom,
                "currentTo" => $currencyTo,
                "commission" => $amount_remove,
                "currency_commission" => $currencyFrom
            ];

        }
        $margin = ExchangeRateMargin::where("from_currency",$currencyFrom)->where("to_currency",$currencyTo)->first();
        $convertingAmount = $margin->rate;
        if($convertingAmount <=0){
            $urlToGet = "https://api.apilayer.com/currency_data/convert?to=".$currencyTo."&from=".$currencyFrom."&amount=1";

            $client = new Client(['verify' => false]);

            try {
                $raw_response = $client->get($urlToGet, [
                    "headers" => [
                        'Content-Type' => 'application/json',
                        'apikey' => env("API_LAYER_CURRENCY_KEY")
                    ]
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $exception) {
                \Log::channel("slack")->error("sendMessage Error to API Currency Converter", [
                    "Response Phrase" => $exception->getResponse()->getReasonPhrase(),
                    "statusCode" => $exception->getResponse()->getStatusCode(),
                    "Message" => $exception->getMessage()
                ]);
                return null;
            }

            $resultAPI =  json_decode($raw_response->getBody()->getContents());
            //$convertingAmount = ( 1 + ($deltaAdded/100)) * $resultAPI->result;
            $convertingAmount = $resultAPI->result;
        }

        $amountTo = self::floor_minus($amount_to_work_with * $convertingAmount, 5);

        return [
            "amountFrom" => $amountFrom,
            "amountTo" => $amountTo,
            "currencyFrom" => $currencyFrom,
            "currentTo" => $currencyTo,
            "commission" => $amount_remove,
            "currency_commission" => $currencyFrom
        ];

    }


    /**
     * @param float $amountTo
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param float $percentageCom
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function convertWithTo(float $amountTo, string $currencyFrom, string $currencyTo, float $percentageCom = 0)
    {
        $converTionRate = self::convertWithFrom(1, $currencyFrom, $currencyTo);

        info("data",["value"=>$converTionRate]);
        $amount_to_work_with = $amountTo/$converTionRate["amountTo"] ;
        $amount_from = $amount_to_work_with / (1 - ($percentageCom/100));
        $amountFrom =  self::ceil_plus($amount_from, 5);
        $amount_remove = $amountFrom * ($percentageCom/100);

        return [
            "amountFrom" => $amountFrom,
            "amountTo" => $amountTo,
            "currencyFrom" => $currencyFrom,
            "currentTo" => $currencyTo,
            "commission" => $amount_remove,
            "currency_commission" => $currencyFrom
        ];
    }


    /**
     * @param float $value
     * @param int|null $precision
     * @return float
     */
    private static function ceil_plus(float $value, ?int $precision = null): float
    {
        if (null === $precision) {
            return (float) ceil($value);
        }
        if ($precision < 0) {
            throw new \RuntimeException('Invalid precision');
        }

        $reg = $value + 0.5 / (10 ** $precision);
        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP);
    }

    /**
     * @param float $value
     * @param int|null $precision
     * @return float
     */
    private static function floor_minus(float $value, ?int $precision = null): float
    {
        if (null === $precision) {
            return (float)floor($value);
        }
        if ($precision < 0) {
            throw new \RuntimeException('Invalid precision');
        }

        $reg = $value - 0.5 / (10 ** $precision);
        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);
    }

}
