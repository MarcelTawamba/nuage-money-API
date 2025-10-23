<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\Achat;
use App\Models\ClientWallet;
use App\Models\CustomExchangeRateMargin;
use App\Models\ExchangeFeeMargin;
use App\Models\ExchangeRateMargin;
use App\Models\ExchangeRequest;
use App\Models\ToupesuPaymentRequest;
use App\Models\Wallet;
use App\Models\WalletType;
use GuzzleHttp\Exception\GuzzleException;
use http\Client;
use phpDocumentor\Reflection\Types\Self_;

class ExchangeHelper
{
    /**
     * @throws \Exception
     * @throws GuzzleException
     */


    static public function exchange($input,$data=null)
    {
        $client = \App\Models\Client::find($input["service"]);

        $currency = WalletType::whereName($input['from_currency'])->first();
        $from_wallet = Wallet::where('user_type',ClientWallet::class)->where('user_id',$client->wallet->id)->where('wallet_type_id', $currency->id)->first();



        if($from_wallet == null || $from_wallet->balance < $input["amount"]){
            return [
                "success"=>false,
                "message"=>"insufficient fund"
            ];
        }


        $market_rate = self::getExchangeRate($input["from_currency"],$input["to_currency"]);
        if($market_rate == null){
            return [
                "success"=>false,
                "message"=>"an error has occur, try latter"
            ];
        }


        $margin = ExchangeRateMargin::where("from_currency",$input["from_currency"])->where("to_currency",$input["to_currency"])->first();

        if(!$margin instanceof  ExchangeRateMargin){
            return [
                "success"=>false,
                "message"=>"Not possible to make this conversion"
            ];
        }

        $custom_margin = CustomExchangeRateMargin::whereExchangeMarginId($margin->id)->where("company_id",$client->company_id)->first();
        if($custom_margin instanceof CustomExchangeRateMargin){
            $margin = $custom_margin;
        }

        if($data == null){
            $result =  ConvertionHelper::convertWithFrom($input["amount"],$input["from_currency"],$input["to_currency"],$margin->margin);
        }else{
            $result = $data;
        }




        /**** Create a new exchange request*/

        $new_exchange_request = new ExchangeRequest();
        $new_exchange_request->client_id = $client->id;
        $new_exchange_request->to_currency = $input["to_currency"];
        $new_exchange_request->from_currency = $input["from_currency"];
        $new_exchange_request->amount = $input["amount"];
        $new_exchange_request->market_rate = $market_rate;
        $new_exchange_request->rate = $market_rate;
        $new_exchange_request->status = PaymentStatus::SUCCESSFUL;
        $new_exchange_request->save();

        /**** Create a new achat to remove fund from the from_wallet */
        $new_achat = new  Achat();
        $new_achat->client_id = $client->id;
        $new_achat->amount = -1 * $new_exchange_request->amount;
        $new_achat->country = "cmr";
        $new_achat->currency = $new_exchange_request->from_currency;
        $new_achat->user_ref_id = self::generateRef();
        $new_achat->ref_id =  self::generateRefCashOut();
        $new_achat->requestable()->associate( $new_exchange_request);
        $new_achat->status= PaymentStatus::SUCCESSFUL;
        $new_achat->save();

        $res = GeneralPaymentHelper::saveTransaction($new_achat);

        if(!$res){
            $new_exchange_request->status = PaymentStatus::FAILED;
            $new_exchange_request->save();

            $new_achat->status= PaymentStatus::FAILED;
            $new_achat->save();

            return [
                "success"=>false,
                "message"=>"an error has occur try later"
            ];
        }

        /**** Create a new achat to remove fund from the from_wallet */
        $new_achat1 = new  Achat();
        $new_achat1->client_id = $client->id;
        $new_achat1->amount = $result["amountTo"];
        $new_achat1->country = "cmr";
        $new_achat1->currency = $new_exchange_request->to_currency;
        $new_achat1->user_ref_id = self::generateRef();
        $new_achat1->ref_id =  self::generateRefCashIn();
        $new_achat1->requestable()->associate( $new_exchange_request);
        $new_achat1->status= PaymentStatus::SUCCESSFUL;
        $new_achat1->save();

        /**** save exchange fee*/

        $new_margin = new ExchangeFeeMargin();

        $new_margin->currency = $new_exchange_request->from_currency;
        $new_margin->amount = $result["commission"];
        $new_margin->exchange_request = $new_exchange_request->id;

        $new_margin->save();

        PayInSuccessEvent::dispatch($new_achat1);

        return [
            "success"=>true,
            "data"=>$new_exchange_request->toArray()
        ];


    }

    /**
     * @throws GuzzleException
     */
    static public function paymentDiffCurrency($input, $client, $currency ): array
    {

        $main_wallet = Wallet::where("user_type",ClientWallet::class)->where('user_id',$client->wallet->id)->where("wallet_type_id",$client->mainCurrency()->id)->first();

        $margin = ExchangeRateMargin::where("from_currency",$main_wallet->currency->name)->where("to_currency",$currency->name)->first();

        if(!$margin instanceof  ExchangeRateMargin){
            return [
                "success"=>false,
                "message"=>"Not possible to make this conversion"
            ];
        }

        $custom_margin = CustomExchangeRateMargin::whereExchangeMarginId($margin->id)->where("company_id",$client->company_id)->first();

        if($custom_margin instanceof CustomExchangeRateMargin){
            $margin = $custom_margin;
        }

        $result =  ConvertionHelper::convertWithTo($input['amount_to'],$main_wallet->currency->name,$currency->name,$margin->margin);


        if( $main_wallet->balance < $result['amountFrom']  ){
            return [
                "success"=>false,
                "message"=>"Insufficient fund"
            ];
        }

        return self::exchange(["service"=>$client->id,"from_currency"=>$result["currencyFrom"],"to_currency"=>$result["currentTo"],'amount' => $result["amountFrom"] ],$result);

    }


    static public function generateRefCashOut(): string
    {

        /**** Create a new ToupesuPaymentRequest object for this user request */


        return "Exchange-CashOut". GeneralPaymentHelper::UUID();

    }
    static public function generateRef(): string
    {

        /**** Create a new ToupesuPaymentRequest object for this user request */


        return "Exchange-". GeneralPaymentHelper::UUID();

    }
    static public function generateRefCashIn(): string
    {

        /**** Create a new ToupesuPaymentRequest object for this user request */


        return "Exchange-CashIn". GeneralPaymentHelper::UUID();

    }

    /**
     * @throws GuzzleException
     */
    static public function getExchangeRate($from, $to)
    {
        $result = GeneralHelper::postToApiLayer($from,$to);
        if($result->is_success){

            return $result->result->result;
        }else{
            return null;
        }

    }

}
