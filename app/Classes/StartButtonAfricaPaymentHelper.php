<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PaymentSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\Achat;
use App\Models\StartButtonPayInRequest;
use App\Models\StartButtonPayOutRequest;
use App\Services\StartButtonAfricaService;
use libphonenumber\NumberParseException;
use function Symfony\Component\Translation\t;
use Illuminate\Support\Facades\Log;

class StartButtonAfricaPaymentHelper extends GeneralPaymentHelper
{

    /**
     * @throws NumberParseException
     */
    static public function initPayment(array $input): \Illuminate\Http\JsonResponse
    {


        $req = Achat::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->first();

        if($req instanceof   Achat){
            return response()->json([
                "success"=> false,
                "message"=>"Duplicate ref_id"
            ]);
        }

        /**** Create a new ToupesuPaymentRequest object for this user request */

        $new_achat = new  Achat();
        $new_achat->client_id = $input["service"];
        $new_achat->amount = $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["ref_id"];
        $new_achat->ref_id = self::generateMomentTime();

        /*** toupesu mobile payment data  */
        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){

            $result = [
                "data" => "http://pay.startbutton.builditdigital.co.s3-website-eu-west-1.amazonaws.com/#/uswfao9b4v",
                "success"=>true
            ];



        }else{
            $startButtonAfricaService = new  StartButtonAfricaService();

            $result = $startButtonAfricaService->requestPayment($new_achat->amount*100 ,$new_achat->ref_id,strtoupper($new_achat->currency) , $input["email"]);

        }




        if($result["success"]){
                /**** save the new ToupesuPaymentRequest object when request created **/

                $new_start_button_request = new StartButtonPayInRequest();
                $new_start_button_request->email = $input['email'];
                $new_start_button_request->payment_link = $result["data"];
                $new_start_button_request->status = PaymentStatus::CREATED;

                $new_start_button_request->save();

                $new_achat->requestable()->associate( $new_start_button_request);
                $new_achat->status= PaymentStatus::CREATED;
                $new_achat->save();

                CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(40));

                /*** return a json respond when request created ***/
                return response()->json([
                    "pay_token"=> $new_achat->ref_id,
                    "amount"=> $new_achat->amount,
                    "ref_id"=> $new_achat->user_ref_id,
                    "payment_link"=> $new_start_button_request->payment_link,
                    "payment_method"=> PaymentMethod::START_BUTTON_BANK,
                    "status"=>$new_achat->status,
                    "success"=>true,

                ]);

        }

        Log::channel("slack")->info("Error when making payin", [
            "Data" => $result
        ]);
        /*** return a json respond when request errors  **/
        return response()->json([
            "success"=>false,
            "pay_token"=> $new_achat->ref_id,
            "ref_id"=> $new_achat->user_ref_id,
            "amount"=> $new_achat->amount,
            "status"=> PaymentStatus::FAILED,
            "message"=> "Request  has failed try latter"
        ]);



    }

    static public function checkRequestPayments(Achat $achat): array
    {

        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){


            $trans = new \stdClass();
            $trans->status = PaymentStatus::SUCCESSFUL;

            $data = new \stdClass();
            $data->transaction =$trans;

            $result = [
                "success"=> true,
                "message"=> "transfer",
                "data"=> $data
            ];
        }else{

            $startButtonAfricaService = new  StartButtonAfricaService();

            $result = $startButtonAfricaService->checkTransaction($achat->ref_id);
        }




        if($result["success"] ){

            if( PaymentStatus::getStatus($result["data"]->transaction->status)  == PaymentStatus::FAILED){

                $achat->status = PaymentStatus::FAILED;
                $achat->requestable->status = PaymentStatus::FAILED;

            }elseif( PaymentStatus::getStatus($result["data"]->transaction->status) == PaymentStatus::SUCCESSFUL){
                // Successful payment
                $achat->status = PaymentStatus::SUCCESSFUL;
                $achat->requestable->status = PaymentStatus::SUCCESSFUL;
                PaymentSuccessEvent::dispatch($achat);

            }else{
                $achat->requestable->status = PaymentStatus::PENDING;

                $achat->status = PaymentStatus::PENDING;
            }

            $achat->save();
            $achat->requestable->save();
            return [
                "pay_token"=> $achat->ref_id,
                "amount"=> $achat->amount,
                "status"=>$achat->status,
                "ref_id"=> $achat->user_ref_id,
                "payment_method"=> PaymentMethod::START_BUTTON_BANK,
            ];
        }


        return [
            "pay_token"=> $achat->ref_id,
            "amount"=> $achat->amount,
            "status"=>$achat->status,
            "ref_id"=> $achat->user_ref_id,
            "payment_method"=> PaymentMethod::START_BUTTON_BANK,
        ];


    }

    static public function checkRequestPayout(Achat $achat): array
    {
        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){


            $trans = new \stdClass();
            $trans->status = PaymentStatus::SUCCESSFUL;

            $data = new \stdClass();
            $data->transaction =$trans;

            $result = [
                "success"=> true,
                "message"=> "transfer",
                "data"=> $data
            ];
        }else{

            $startButtonAfricaService = new  StartButtonAfricaService();

            $result = $startButtonAfricaService->checkTransaction($achat->ref_id);
        }
        Log::channel("slack")->info("StartButtonWebHookController Data is OK and recevied", [
            "Data" => $result
        ]);


        if($result["success"] ){

            if( PaymentStatus::getStatus($result["data"]->transaction->status) == PaymentStatus::FAILED){

                $achat->status = PaymentStatus::FAILED;
                $achat->requestable->status = PaymentStatus::FAILED;
                PayOutFailureEvent::dispatch($achat);

            }elseif (PaymentStatus::getStatus($result["data"]->transaction->status) ==   PaymentStatus::SUCCESSFUL){
                // Successful payment
                $achat->status = PaymentStatus::SUCCESSFUL;
                $achat->requestable->status = PaymentStatus::SUCCESSFUL;


            }else{
                $achat->requestable->status = PaymentStatus::PENDING;

                $achat->status = PaymentStatus::PENDING;
            }

            $achat->save();
            $achat->requestable->save();
            return [
                "pay_token"=> $achat->ref_id,
                "amount"=> $achat->amount,
                "status"=>$achat->status,
                "ref_id"=> $achat->user_ref_id,
                "payment_method"=> PaymentMethod::START_BUTTON_BANK,
                "success"=>true
            ];
        }


        return [
            "pay_token"=> $achat->ref_id,
            "amount"=> $achat->amount,
            "status"=>$achat->status,
            "ref_id"=> $achat->user_ref_id,
            "success"=>true,
            "payment_method"=> PaymentMethod::START_BUTTON_BANK,
        ];
    }

    static public function initPayout(array $input): \Illuminate\Http\JsonResponse
    {


        /**** Create a new ToupesuPaymentRequest object for this user request */

        $new_achat = new   Achat();
        $new_achat->client_id = $input["service"];
        $new_achat->amount = -1 * $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["ref_id"];
        $new_achat->ref_id = self::generateMomentTime();



        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $result = [
                "success"=> true,
                "message"=>"transfer",
                "data"=> "processing"
            ];

        }else{

            $startButtonAfricaService = new  StartButtonAfricaService();
            $account = self::verifyAccount($input);

            if(!$account["success"]){
                return response()->json($account);
            }

            $result = $startButtonAfricaService->makeTransfert($input["amount"] * 100, $input["bank_code"],$input["account_number"],$new_achat->ref_id,strtoupper($new_achat->currency));
        }



        if($result["success"]  ){

                /**** save the new ToupesuPaymentRequest object when request created **/

                $new_start_button_request = new StartButtonPayOutRequest();
                $new_start_button_request->account_name = $input["account_name"];
                $new_start_button_request->account_number = $input["account_number"];
                $new_start_button_request->status = PaymentStatus::CREATED;
                $new_start_button_request->bank_code = $input["bank_code"];

                $new_start_button_request->save();

                $new_achat->requestable()->associate( $new_start_button_request);
                $new_achat->status= PaymentStatus::CREATED;
                $new_achat->save();

                $res = self::saveTransaction($new_achat);

                CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(20));

                /*** return a json respond when request created ***/
                return response()->json([
                    "pay_token"=> $new_achat->ref_id,
                    "amount"=> -1 * $new_achat->amount,
                    "ref_id"=> $new_achat->user_ref_id,
                    "payment_method"=> PaymentMethod::START_BUTTON_BANK,
                    "status"=>$new_achat->status,
                    "success"=>true
                ]);

        }

        /*** return a json respond when request errors  **/
        Log::channel("slack")->info("Error when making payout", [
            "Data" => $result
        ]);
        return response()->json([
            "pay_token"=> $new_achat->ref_id,
            "ref_id"=> $new_achat->user_ref_id,
            "amount"=> -1 * $new_achat->amount,
            "status"=> PaymentStatus::FAILED,
            "message"=> "Payment has failed try latter",
            "success"=>false
        ]);


    }

    static public function verifyAccount(array $input): array
    {
        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $result = [
                "success"=> true,
                "data"=> "Account available",

            ];
        }else{

            $startButtonAfricaService = new  StartButtonAfricaService();

            $account = $startButtonAfricaService->bankAccountValidation($input["bank_code"],$input["account_number"]);


            if($account["success"]){
                similar_text(strtolower($input["account_name"]), strtolower($account["data"]->account_name),$percent );
                if($percent < 80){
                    $result = [
                        "success"=> false,
                        "message"=> "Information does not match",
                    ];
                }else{
                    $result = [
                        "success"=> true,
                        "data"=> "Account valid",
                        "message"=> "Account available",

                    ];
                }

            }else{
                $result = [
                    "success"=> false,
                    "message"=> "Account not resolved",
                ];
            }
        }

        return  $result;
    }

    public static function generateMomentTime(): string
    {

        return "StartButton-". parent::UUID();

    }
}
