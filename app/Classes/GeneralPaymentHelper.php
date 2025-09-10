<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PayType;
use App\Models\Achat;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\CountryAvaillable;
use App\Models\CustomFee;
use App\Models\ExchangeRateMargin;
use App\Models\ExchangeRequest;
use App\Models\Operator;
use App\Models\SystemLedger;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletType;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

abstract class GeneralPaymentHelper
{

    abstract static public function initPayment(array $input);

    abstract static public function checkRequestPayments(Achat $achat);

    abstract static public function checkRequestPayout(Achat $achat);

    abstract static public  function  initPayout(array $input);

    abstract static public  function  generateMomentTime();


    /**
     * @throws Exception
     * @throws GuzzleException
     */
    static public function checkPayments($input): \Illuminate\Http\JsonResponse
    {

        $request = Achat::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->where("amount",">",0)->first();

        if(!($request instanceof  Achat)){
            return response()->json([
                "message"=>__("does_not_exist",["attribute"=>"ref_id"]),
                "errors"=>[
                    "ref_id"=>__("does_not_exist",["attribute"=>"ref_id"])
                ]
            ]);
        }

        if($request->status == PaymentStatus::PENDING || $request->status == PaymentStatus::CREATED){
            return response()->json(static::checkRequestPayments($request));
        }

        return response()->json([
            "pay_token"=> $request->ref_id,
            "amount"=> $request->amount,
            "status"=>$request->status,
            "ref_id"=> $request->user_ref_id,
            "payment_method"=> $input["payment_method"],
        ]);


    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    static public function checkPayout($input): \Illuminate\Http\JsonResponse
    {

        $request = Achat::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->where("amount","<",0)->first();

        if(!($request instanceof  Achat)){
            return response()->json([
                "message"=>__("does_not_exist",["attribute"=>"ref_id"]),
                "errors"=>[
                    __("common.does_not_exist",["attribute"=>"ref_id"])
                ]
            ]);
        }



        if($request->status == PaymentStatus::PENDING || $request->status == PaymentStatus::CREATED){

            return response()->json(static::checkRequestPayout($request));
        }

        return response()->json([
            "pay_token"=> $request->ref_id,
            "amount"=> $request->amount,
            "status"=>$request->status,
            "ref_id"=> $request->user_ref_id,
            "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
        ]);

    }

    public static function UUID(): string
    {

        return Str::uuid();

    }


    public static function saveTransaction(Achat $achat): bool
    {

        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($achat) {

                $currency= WalletType::where("name",$achat->currency)->first();

                $client = Client::find($achat->client_id);
                $client_wallet  = $client->wallet;
                if( !($client_wallet instanceof ClientWallet)){
                    $client_wallet = new  ClientWallet();
                    $client_wallet->client_id = $client->id;
                    $client_wallet->save();

                }


                $wallet = Wallet::where('user_type',ClientWallet::class)->where('user_id',$client->wallet->id)->where('wallet_type_id', $currency->id)->first();

                $system_wallet =  Wallet::where('user_type',SystemLedger::class)->where('user_id',SystemLedger::whereName("system")->first()->id)->where('wallet_type_id', $currency->id)->first();
                $system_fee_wallet =  Wallet::where('user_type',SystemLedger::class)->where('user_id',SystemLedger::whereName("system fee")->first()->id)->where('wallet_type_id', $currency->id)->first();

                $country = CountryAvaillable::where("code", $achat->country)->first();

                if($achat->requestable_type == ExchangeRequest::class){
                    $total_fee_amount = 0;

                }else{
                    $fees =  Operator::where("currency_id",$currency->id)->where("country_id",$country->id)->where("type",PayType::PAY_OUT)->first();
                    $custom_fee = CustomFee::where("company_id",$client->company_id)->where("method_id",$fees->id)->first();

                    if($custom_fee instanceof  CustomFee){
                        $fees = $custom_fee;
                    }

                    $total_fee_amount = $fees->fee_type == "percentage" ?  -1 *$achat->amount * ( $fees->fees / 100 ) : $fees->fees;
                }


                $total_fee_amount = round($total_fee_amount,2);

                // Transaction to remove fund from client wallet
                $new_transaction = new Transaction();
                $new_transaction->reference = $achat->ref_id;
                $new_transaction->amount = $achat->amount;
                $new_transaction->wallet_id = $wallet->id;
                $new_transaction->balance_before= $wallet->balance  ;
                $new_transaction->balance_after = $wallet->balance + $achat->amount ;
                $new_transaction->description = "Cash out from ".$achat->ref_id;
                $new_transaction->achatable()->associate($achat);
                $new_transaction->save();

                $wallet->decrementBalance(-1*$achat->amount);

                // Transaction to remove fund from system wallet
                $new_transaction_to_system = new Transaction();
                $new_transaction_to_system->reference = $achat->ref_id;
                $new_transaction_to_system->amount = $achat->amount;
                $new_transaction_to_system->wallet_id = $system_wallet->id;
                $new_transaction_to_system->balance_after = $system_wallet->balance  + $achat->amount;
                $new_transaction_to_system->balance_before = $system_wallet->balance ;
                $new_transaction_to_system->achatable()->associate($achat);
                $new_transaction_to_system->description = "System wallet Cash out from ".$achat->ref_id;
                $new_transaction_to_system->save();
                $system_wallet->decrementBalance(-1*$achat->amount);

                // Transaction to collect fee from client wallet
                $new_transaction_get_system_fee_from_wallet = new Transaction();
                $new_transaction_get_system_fee_from_wallet->reference = $achat->ref_id;
                $new_transaction_get_system_fee_from_wallet->amount = -1 * $total_fee_amount;
                $new_transaction_get_system_fee_from_wallet->wallet_id = $wallet->id;
                $new_transaction_get_system_fee_from_wallet->balance_after = $wallet->balance - $total_fee_amount;
                $new_transaction_get_system_fee_from_wallet->balance_before = $wallet->balance ;
                $new_transaction_get_system_fee_from_wallet->achatable()->associate($new_transaction);
                $new_transaction_get_system_fee_from_wallet->description = "Fees collected for transaction ".$new_transaction->reference;
                $new_transaction_get_system_fee_from_wallet->save();
                $wallet->decrementBalance($total_fee_amount);

                // Transaction to fund system fee wallet
                $new_transaction_to_system_fee = new Transaction();
                $new_transaction_to_system_fee->reference = $achat->ref_id;
                $new_transaction_to_system_fee->amount = $total_fee_amount;
                $new_transaction_to_system_fee->wallet_id = $system_fee_wallet->id;
                $new_transaction_to_system_fee->balance_after = $system_fee_wallet->balance + $total_fee_amount;
                $new_transaction_to_system_fee->balance_before = $system_fee_wallet->balance ;
                $new_transaction_to_system_fee->achatable()->associate($new_transaction_get_system_fee_from_wallet);
                $new_transaction_to_system_fee->description = "Fees receive for the transaction ".$new_transaction->reference;
                $new_transaction_to_system_fee->save();
                $system_fee_wallet->incrementBalance($total_fee_amount);



            });

            return true;
        }catch (Exception|\Throwable $e) {

            info("Error has occur",["data"=>$achat,"error"=>$e]);
            return false;


        }


    }





}
