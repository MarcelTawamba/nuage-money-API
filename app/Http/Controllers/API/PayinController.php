<?php

namespace App\Http\Controllers\API;

use App\Classes\ExchangeHelper;
use App\Classes\ToupesuGeneralPaymentHelpers;
use App\Enums\MethodType;
use App\Enums\PaymentMethod;
use App\Enums\PayType;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CheckPaymentRequest;
use App\Http\Requests\API\CheckWalletBalance;
use App\Http\Requests\API\MakePaymentRequest;
use App\Classes\ToupesuPhoneNumber;
use App\Http\Requests\API\MakePayoutRequest;
use App\Models\Achat;
use App\Models\ClientWallet;
use App\Models\CountryAvaillable;
use App\Models\CustomFee;
use App\Models\Operator;
use App\Models\Transaction;

use CoreProc\WalletPlus\Models\Wallet;
use App\Models\WalletType;
use GuzzleHttp\Exception\GuzzleException;

use Illuminate\Http\Request;
use libphonenumber\NumberParseException;



class PayinController extends AppBaseController
{
    //

    public function __construct()
    {


    }

    /**
     * @OA\Post(
     *     path="/v1/make-mobile-payment",
     *     operationId="make-payment",
     *     tags={"Mobile Payment"},
     *     security={{"bearerAuth":{}}},
     *     summary="Il est question d'initier un paiement avec une passerelle de paiement Mobile
     *       En effet, vous devez effectuer une requête POST en passant dans le corps de votre
     *       requête des données formatées en JSON",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\MakePaymentRequestSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\PaymentResponseResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *
     * ),
     * Display a listing of the Transaction.
     * @throws NumberParseException
     * @throws GuzzleException
     */
    public function makePayment(MakePaymentRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();


        if(isset($input["msidn"])){


           $phone = new  ToupesuPhoneNumber($input["msidn"]);

           if( !$phone->IsValidNumber()){
               return $this->sendValidationError(["msidn" => [__("validation.regex",["attribute"=>"msidn"])]],__("validation.regex",["attribute"=>"msidn"]));
           }

           $country= CountryAvaillable::where("code",$input["country"])->first();
           $currency= WalletType::where("name",$input["currency"])->first();

           $method = Operator::where("currency_id",$currency->id)->where("country_id",$country->id)->where("method_type",MethodType::MOBILE)->where("type",PayType::PAY_IN)->first();
           if($method instanceof  Operator){
               if($method->method_class == PaymentMethod::TOUPESU_MOBILE){


                   return ToupesuGeneralPaymentHelpers::initPayment($input);

               }
           }

        }

        return \response()->json(["success"=>false,"message"=>__("common.some_thing_went_wrong")]);

    }
    /**
     * @OA\Post(
     *     path="/v1/check-mobile-payment",
     *     operationId="check-mobile-payment",
     *     tags={"Mobile Payment"},
     *     security={{"bearerAuth":{}}},
     *     summary="Use to verify pay in request status",
     *     description="Use to verify payout request status. We have 4 different status which are : <br/>
            - CREATED (when the request is initialize) <br/>
            - PENDING (when the request in being process) <br/>
            - SUCCESSFUL (when the request has been completed successfully) <br/>
            - FAILED (when the request has failed)",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\CheckRequestStatusSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\PaymentResponseResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the Transaction.
     * @throws GuzzleException
     */
    public function checkPayment(CheckPaymentRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $client = auth('api')->client();


        if($client->id != $input["service"]){
            return response()->json(["message"=>__("common.unauthenticated")]);

        }

        switch ($input["payment_method"]){
            case PaymentMethod::TOUPESU_MOBILE :
                return ToupesuGeneralPaymentHelpers::checkPayments($input);
        }

        return \response()->json(["message"=>__("common.some_thing_went_wrong")]);

    }

    /**
     *
     * @OA\Post(
     *      path="/v1/check-balance",
     *      operationId="check-balance",
     *      tags={"Global"},
     *      security={{"bearerAuth":{}}},
     *      summary="Check service balance for a particular currency",
     *      description="Check service balance for a particular currency",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\CheckWalletRequestSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\PaymentResponseResource")
     *
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the Transaction.
     * @throws GuzzleException
     */
    public function checkWalletBalance(CheckWalletBalance $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $client = auth('api')->client();


        if($client->id != $input["service"]){
            return response()->json(["message"=>__("common.unauthenticated")]);

        }
        $currency= WalletType::where("name",$input["currency"])->first();

        $wallet = Wallet::where("user_type",ClientWallet::class)->where("user_id",$client->wallet->id)->where("wallet_type_id",$currency->id)->first();

        if($wallet instanceof Wallet){

            return \response()->json(["success"=>true,"service"=> $client->id,"currency"=>$currency->name,"amount"=>$wallet->balance]);
        }

        return \response()->json(["success"=>true,"service"=> $client->id,"currency"=>$currency->name,"amount"=> 0]);

    }

    /**
     * @OA\Post(
     *     path="/v1/make-mobile-payout",
     *     operationId="make-mobile-payout",
     *     tags={"Mobile Payout"},
     *     security={{"bearerAuth":{}}},
     *     summary="This request is use to initiate a payout request ",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\MakePayoutRequestSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\PaymentResponseResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the Transaction.
     * @throws GuzzleException
     * @throws NumberParseException
     */
    public function payout(MakePayoutRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $client = auth('api')->client();


        if($client->id != $input["service"]){
            return response()->json(['success'=>false,"message"=>__("common.unauthenticated")]);

        }

        /**** check if ref_id exist for this service **/
        $req = Achat::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->first();

        if($req instanceof   Achat){
            return response()->json([
                "success"=> false,
                "message"=>"Duplicate ref_id"
            ]);
        }
        if( strtoupper($client->main_wallet) == strtoupper($input["currency"])){
            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"msidn"=>$input["msidn"],"amount"=>$input["amount"],"error"=>"Cashout not available for this currency"]);

        }

        $currency= WalletType::where("name",$input["currency"])->first();


        if( !($client->wallet instanceof ClientWallet)){

            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"msidn"=>$input["msidn"],"amount"=>$input["amount"],"error"=>__("common.not_sufficient_fund")]);
        }

        $wallet = \App\Models\Wallet::where("user_type",ClientWallet::class)->where('user_id',$client->wallet->id)->where("wallet_type_id",$currency->id)->first();


        if( !($wallet instanceof Wallet)){

            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"msidn"=>$input["msidn"],"amount"=>$input["amount"],"error"=>__("common.not_sufficient_fund")]);
        }

        $country= CountryAvaillable::where("code",$input["country"])->first();

        $method = Operator::where("currency_id",$currency->id)->where("country_id",$country->id)->where("type",PayType::PAY_OUT)->first();

        $fees = $method;
        $custom_fee = CustomFee::where("company_id",$client->company_id)->where("method_id",$method->id)->first();

        if($custom_fee instanceof  CustomFee){
            $fees = $custom_fee;
        }

        $total_fee_amount = $fees->fee_type == "percentage" ?  -1 * $input["amount"]* ( $fees->fees / 100 ) : $fees->fees;

        if( $wallet->balance < ($input["amount"] + $total_fee_amount)){
            $input["amount_to"] = ($input["amount"] +  $total_fee_amount);
            $result =  ExchangeHelper::paymentDiffCurrency($input,$client,$currency);

            if(!$result["success"]){
                return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"msidn"=>$input["msidn"],"amount"=>$input["amount"],"error"=>__("common.not_sufficient_fund")]);
            }

        }


        if(isset($input["msidn"])){

            $phone = new  ToupesuPhoneNumber($input["msidn"]);

            if( !$phone->IsValidNumber()){
                return $this->sendValidationError(["msidn" => [__("validation.regex",["attribute"=>"msidn"])]],__("validation.regex",["attribute"=>"msidn"]));

            }


            if($method instanceof  Operator){
                if($method->method_class == PaymentMethod::TOUPESU_MOBILE){
                    $input["carrier"] = $phone->carrier;


                    return ToupesuGeneralPaymentHelpers::initPayout($input);

                }
            }

        }

        return \response()->json(['success'=>false,"message"=>__("common.some_thing_went_wrong")]);
    }

    /**
     * @OA\Post(
     *     path="/v1/check-mobile-payout",
     *     operationId="check-mobile-payout",
     *     tags={"Mobile Payout"},
     *     security={{"bearerAuth":{}}},
     *     summary="Use to verify payout request status",
     *     description="Use to verify payout request status. We have 4 different status which are : <br/>
            - CREATED (when the request is initialize) <br/>
            - PENDING (when the request in being process) <br/>
            - SUCCESSFUL (when the request has been completed successfully) <br/>
            - FAILED (when the request has failed)",
     *
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\CheckRequestStatusSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\PaymentResponseResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the Transaction.
     * @throws NumberParseException
     * @throws GuzzleException
     */
    public function checkPayout(CheckPaymentRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $client = auth('api')->client();


        if($client->id != $input["service"]){
            return response()->json(['success'=>false,"message"=>__("common.unauthenticated")]);

        }

        switch ($input["payment_method"]){
            case PaymentMethod::TOUPESU_MOBILE :
                return ToupesuGeneralPaymentHelpers::checkPayout($input);
        }

        return \response()->json(['success'=>false,"message"=>__("common.some_thing_went_wrong")]);


    }

    public function sendValidationError($errors, $message): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success'=>false,"message"=>$message,"errors"=>$errors]);
    }

    /**
     *
     * @OA\Post(
     *      path="/v1/app-wallets",
     *      operationId="app-wallets",
     *      tags={"Global"},
     *      security={{"bearerAuth":{}}},
     *      summary="List of all the wallet link to a particular app",
     *      description="List of all the wallet link to a particular app",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\AppWalletResponseResource")
     *
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the Transaction.
     * @throws GuzzleException
     */
    public function walletsBalance(Request $request): \Illuminate\Http\JsonResponse
    {


        $client = auth('api')->client();

        $wallets = Wallet::where("user_id",$client->wallet->id)->where("user_type",ClientWallet::class)->get();


        return \response()->json(["success"=>true,"data"=>$wallets->toArray()]);

    }

    /**
     *
     * @OA\Post(
     *      path="/v1/app-transaction",
     *      operationId="app-transaction",
     *      tags={"Global"},
     *      security={{"bearerAuth":{}}},
     *      summary="List of all the wallet link to a particular app",
     *      description="List of all the wallet link to a particular app",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\AppTransactionResponseResource")
     *
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the Transaction.
     * @throws GuzzleException
     */
    public function appTransaction(Request $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->all();


        $client = auth('api')->client();

        $wallets = Wallet::where("user_id",$client->wallet->id)->where("user_type",ClientWallet::class)->get();


        $wallet_id = [];

        foreach ($wallets as $wat){
            $wallet_id[] = $wat->id;
        }



        $transactions = Transaction::whereIn("wallet_id",$wallet_id)->where("refund",false)->get();
        $lists = [];
        foreach ($transactions as $transaction){
            if($transaction->achatable->user_ref_id != null){
                $lists [] = $transaction->toApiArray();
            }

        }


        return \response()->json(["success"=>true,"data"=>$lists]);

    }

}
