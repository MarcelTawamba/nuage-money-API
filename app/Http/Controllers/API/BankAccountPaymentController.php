<?php

namespace App\Http\Controllers\API;

use App\Classes\ExchangeHelper;
use App\Classes\StartButtonAfricaPaymentHelper;
use App\Enums\MethodType;
use App\Enums\PaymentMethod;
use App\Enums\PayType;
use App\Http\Requests\API\CheckPaymentRequest;
use App\Http\Requests\API\GetBankCodeRequest;
use App\Http\Requests\API\MakeBankAccountPayInRequest;
use App\Http\Requests\API\MakeBankAcountPayOutRequest;
use App\Http\Requests\API\VerifyBankRequest;
use App\Models\Achat;
use App\Models\ClientWallet;
use App\Models\CountryAvaillable;
use App\Models\CustomFee;
use App\Models\Operator;
use App\Models\StartButtonBank;
use App\Models\Wallet;
use App\Models\WalletType;
use GuzzleHttp\Exception\GuzzleException;
use libphonenumber\NumberParseException;

class BankAccountPaymentController extends \App\Http\Controllers\AppBaseController
{

    public function __construct()
    {


    }

    /**
     * @OA\Post(
     *     path="/v1/make-bank-payment",
     *     operationId="make-bank-payment",
     *     tags={"Bank Payment"},
     *     security={{"bearerAuth":{}}},
     *     summary="Il est question d'initier un paiement avec une passerelle de paiement Mobile
     *       En effet, vous devez effectuer une requête POST en passant dans le corps de votre
     *       requête des données formatées en JSON",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\MakeBankPaymentRequestSchema")
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
     * @throws NumberParseException
     */
    public function makePayment(MakeBankAccountPayInRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $country= CountryAvaillable::where("code",$input["country"])->first();
        $currency= WalletType::where("name",$input["currency"])->first();

        $client = auth('api')->client();

        if( strtoupper($client->main_wallet) != strtoupper($input["currency"])){
            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"amount"=>$input["amount"],"error"=>"Cashout not available for this currency"]);

        }

        $method = Operator::where("currency_id",$currency->id)->where("country_id",$country->id)->where("method_type",MethodType::BANK_ACCOUNT)->where("type",PayType::PAY_IN)->first();
        if($method instanceof  Operator){
            if($method->method_class == PaymentMethod::START_BUTTON_BANK){

                return StartButtonAfricaPaymentHelper::initPayment($input);

            }
        }


        return \response()->json(["message"=>__("common.some_thing_went_wrong")]);

    }

    /**
     * @OA\Post(
     *     path="/v1/check-bank-payment",
     *     operationId="check-bank-payment",
     *     tags={"Bank Payment"},
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
            case PaymentMethod::START_BUTTON_BANK :
                return StartButtonAfricaPaymentHelper::checkPayments($input);
        }

        return \response()->json(["message"=>__("common.some_thing_went_wrong")]);

    }

    /**
     * @OA\Post(
     *     path="/v1/make-bank-payout",
     *     operationId="make-bank-payout",
     *     tags={"Bank Payout"},
     *     security={{"bearerAuth":{}}},
     *     summary="This request is use to initiate a payout request ",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\MakeBankPayoutRequestSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\BankPaymentResponseResource")
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
    public function payout(MakeBankAcountPayOutRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();
        $client = auth('api')->client();


        if($client->id != $input["service"]){
            return response()->json(["message"=>__("common.unauthenticated")]);

        }

        /**** check if ref_id exist for this service **/
        $req = Achat::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->first();

        if($req instanceof   Achat){
            return response()->json([
                "success"=> false,
                "message"=>"Duplicate ref_id"
            ]);
        }

        if( !($client->wallet instanceof ClientWallet)){

            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"account_number"=>$input["account_number"],"account_name"=>$input["account_name"],"bank_code"=>$input["bank_code"],"amount"=>$input["amount"],"error"=>__("common.not_sufficient_fund")]);
        }
        $country= CountryAvaillable::where("code",$input["country"])->first();
        $currency= WalletType::where("name",$input["currency"])->first();

        $method = Operator::where("currency_id",$currency->id)->where("country_id",$country->id)->where("method_type",MethodType::BANK_ACCOUNT)->where("type",PayType::PAY_OUT)->first();

        if( !($method instanceof Operator)){

            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"account_number"=>$input["account_number"],"account_name"=>$input["account_name"],"bank_code"=>$input["bank_code"],"amount"=>$input["amount"],"error"=>"Method not found check currency or country"]);
        }

        $wallet = Wallet::where("user_type",ClientWallet::class)->where('user_id',$client->wallet->id)->where("wallet_type_id",$currency->id)->first();

        if( !($wallet instanceof Wallet)){

            return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"account_number"=>$input["account_number"],"account_name"=>$input["account_name"],"bank_code"=>$input["bank_code"],"amount"=>$input["amount"],"error"=>__("common.not_sufficient_fund")]);
        }

        $fees = $method;
        $custom_fee = CustomFee::where("company_id",$client->company_id)->where("method_id",$method->id)->first();

        if($custom_fee instanceof  CustomFee){
            $fees = $custom_fee;
        }

        $total_fee_amount = $fees->fee_type == "percentage" ?  -1 * $input["amount"]* ( $fees->fees / 100 ) : $fees->fees;

        if( $wallet->balance < ($input["amount"] +  $total_fee_amount) ){
            $input["amount_to"] = ($input["amount"] +  $total_fee_amount);
            $result =  ExchangeHelper::paymentDiffCurrency($input,$client,$currency);
            info("Convert",['data'=>$result]);
            if(!$result["success"]){
                return \response()->json(["success"=>false,"service"=> $client->id,"currency"=>$input["currency"],"ref_id"=>$input["ref_id"],"account_number"=>$input["account_number"],"account_name"=>$input["account_name"],"bank_code"=>$input["bank_code"],"amount"=>$input["amount"],"error"=>__("common.not_sufficient_fund")]);
            }
        }



        if($method instanceof  Operator){
            if($method->method_class == PaymentMethod::START_BUTTON_BANK){

                return StartButtonAfricaPaymentHelper::initPayout($input);

            }
        }

        return \response()->json(["message"=>__("common.some_thing_went_wrong")]);

    }

    /**
     * @OA\Post(
     *     path="/v1/check-bank-payout",
     *     operationId="check-bank-payout",
     *     tags={"Bank Payout"},
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
            return response()->json(["message"=>__("common.unauthenticated")]);

        }

        switch ($input["payment_method"]){
            case PaymentMethod::START_BUTTON_BANK :
                return StartButtonAfricaPaymentHelper::checkPayout($input);
        }

        return \response()->json(["message"=>__("common.some_thing_went_wrong")]);


    }

    /**
     * @OA\Post(
     *     path="/v1/verify-account",
     *     operationId="verify-account",
     *     tags={"Bank Payout"},
     *     security={{"bearerAuth":{}}},
     *     summary="Use to verify payout request status",
     *     description="Use to verify payout request status. We have 4 different status which are",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\VerifyAccountRequestSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\VerifyAccountResponseResource")
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
    public function verifyBankAccount(VerifyBankRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();

        return \response()->json(StartButtonAfricaPaymentHelper::verifyAccount($input));


    }

    /**
     * @OA\Post(
     *     path="/get-bank-code",
     *     operationId="get-bank-code",
     *     tags={"Global"},
     *     summary="Use to verify payout request status",
     *     description="Use to verify payout request status",
     *
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="App\Virtual\BankCodeRequestSchema")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\BankCodeResponseResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of Bank code.
     * @throws NumberParseException
     * @throws GuzzleException
     */
    public function getBankCode(GetBankCodeRequest $request): \Illuminate\Http\JsonResponse
    {

        $input = $request->all();

        $code = StartButtonBank::where("currency",$input["currency"])->get();

        return \response()->json($code->toArray());


    }
}
