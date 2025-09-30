<?php

namespace App\Classes;


use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\Achat;
use App\Models\ToupesuAccessToken;
use App\Models\ToupesuPaymentRequest;
use GuzzleHttp\Exception\GuzzleException;


class ToupesuGeneralPaymentHelpers extends GeneralPaymentHelper
{
    /**
     * @throws \Exception
     * @throws GuzzleException
     */


    static public function initPayment($input): \Illuminate\Http\JsonResponse
    {
        /**** check if ref_id exist for this service **/
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

        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $phone = new  ToupesuPhoneNumber($input["msidn"]);
            $result = new  \stdClass();

            $result->is_success = true;
            $result->result = new  \stdClass();
            $result->result->success = "true";
            $result->result->pay_token = parent::UUID();
            $result->result->paymentMethod = $phone->carrier;

        }else{
            /*** toupesu mobile payment data  */

            $data = new  \stdClass();

            $data->msidn = $input['msidn'];
            $data->amount = $new_achat->amount;
            $data->moneyCode = $new_achat->currency ;
            $data->refID = $new_achat->ref_id;
            $data->product = env("TOUPESU_PRODUCT");

            /*** get access token ***/
            $token = self::getToupesuToken();

            /*** post the request to toupesu ***/
            $result = GeneralHelper::postTo(env("TOUPESU_ROOT_URL") .'/api/main/reqPayment',$data,$token);
        }



        if($result->is_success  ){

            if($result->result->success == "true"){
                /**** save the new ToupesuPaymentRequest object when request created **/


                $new_toupesu_request = new ToupesuPaymentRequest();
                $new_toupesu_request->msidn = $input['msidn'];
                $new_toupesu_request->pay_token = $result->result->pay_token;
                $new_toupesu_request->status = PaymentStatus::CREATED;
                $new_toupesu_request->payment_method = $result->result->paymentMethod;

                $new_toupesu_request->save();

                $new_achat->requestable()->associate( $new_toupesu_request);
                $new_achat->status= PaymentStatus::CREATED;
                $new_achat->save();

                CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(20));

                /*** return a json respond when request created ***/
                return response()->json([
                    "pay_token"=> $new_achat->ref_id,
                    "amount"=> $new_achat->amount,
                    "ref_id"=> $new_achat->user_ref_id,
                    "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
                    "status"=>$new_achat->status,
                    "success"=>true

                ]);

            }


        }

        /*** return a json respond when request errors  **/
        return response()->json([
            "pay_token"=> $new_achat->ref_id,
            "ref_id"=> $new_achat->user_ref_id,
            "amount"=> $new_achat->amount,
            "status"=> PaymentStatus::FAILED,
            "success"=>false,
        ]);


    }


    /**
     * @throws \Exception
     * @throws GuzzleException
     */
    static public function checkRequestPayments(Achat $achat): array
    {



        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $result = new  \stdClass();

            $result->is_success = true;
            $result->result = new  \stdClass();
            $result->result->success = "true";
            $result->result->status = "COMPLETED";
        }else{
            $data = new  \stdClass();

            $data->paymentMethod = $achat->requestable->payment_method ;
            $data->refID = $achat->ref_id;
            $data->product =  env("TOUPESU_PRODUCT");
            $token = self::getToupesuToken();

            $result = GeneralHelper::postTo(env("TOUPESU_ROOT_URL") .'/api/main/checkTransation',$data,$token);
        }



        if($result->is_success && $result->result->success =="true" ){

            if($result->result->status=="FAILED"){

                $achat->status = PaymentStatus::FAILED;
                $achat->requestable->status = PaymentStatus::FAILED;

            }elseif ($result->result->status=="COMPLETED"){
                // Successful payment
                $achat->status = PaymentStatus::SUCCESSFUL;
                $achat->requestable->status = PaymentStatus::SUCCESSFUL;
                PayInSuccessEvent::dispatch($achat);

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
                "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
                "success"=>true
            ];
        }


        return [
            "pay_token"=> $achat->ref_id,
            "amount"=> $achat->amount,
            "status"=>$achat->status,
            "ref_id"=> $achat->user_ref_id,
            "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
            "success"=>true
        ];


    }


    /**
     * @throws GuzzleException
     */
    static public function initPayout($input): \Illuminate\Http\JsonResponse
    {


        /**** Create a new ToupesuPaymentRequest object for this user request */

        $new_achat = new   Achat();
        $new_achat->client_id = $input["service"];
        $new_achat->amount = - $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["ref_id"];
        $new_achat->ref_id = self::generateMomentTime();



        /*** toupesu mobile payment data  */
        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $phone = new  ToupesuPhoneNumber($input["msidn"]);
            $result = new  \stdClass();

            $result->is_success = true;
            $result->result = new  \stdClass();
            $result->result->success = true;
            $result->result->request_token = parent::UUID();
            $result->result->operator = $phone->carrier;

        }else{
            $data = new  \stdClass();

            $data->msidn = $input["msidn"];
            $data->amount = $input["amount"];
            $data->currency = $new_achat->currency ;
            $data->refID = $new_achat->ref_id;

            /*** get access token ***/
            $token = self::getToupesuToken();

            $result = GeneralHelper::postTo(env("TOUPESU_ROOT_URL") .'/api/disbursement/send',$data,$token);

        }



        if($result->is_success  ){

            if($result->result->success){
                /**** save the new ToupesuPaymentRequest object when request created **/

                $new_toupesu_request = new ToupesuPaymentRequest();
                $new_toupesu_request->msidn = $input['msidn'];
                $new_toupesu_request->pay_token = $result->result->request_token;
                $new_toupesu_request->status = PaymentStatus::CREATED;
                $new_toupesu_request->payment_method = $result->result->operator;

                $new_toupesu_request->save();

                $new_achat->requestable()->associate( $new_toupesu_request);
                $new_achat->status= PaymentStatus::CREATED;
                $new_achat->save();

                $res = self::saveTransaction($new_achat);

                CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(20));

                /*** return a json respond when request created ***/
                return response()->json([
                    "pay_token"=> $new_achat->ref_id,
                    "amount"=> -1 * $new_achat->amount,
                    "ref_id"=> $new_achat->user_ref_id,
                    "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
                    "status"=>$new_achat->status,
                    "success"=>true

                ]);

            }


        }

        /*** return a json respond when request errors  **/

        return response()->json([
            "pay_token"=> $new_achat->ref_id,
            "ref_id"=> $new_achat->user_ref_id,
            "amount"=> -1 * $new_achat->amount,
            "status"=> PaymentStatus::FAILED,
            "message"=>$result->result->error,
            "success"=>false
        ]);

    }



    /**
     * @throws \Exception
     * @throws GuzzleException
     */
    static public function checkRequestPayout(Achat $achat): array
    {
        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $result = new  \stdClass();

            $result->is_success = true;
            $result->result = new  \stdClass();
            $result->result->success = true;
            $result->result->status = "COMPLETED";
        }else{
            $data = new  \stdClass();


            $data->refID = $achat->ref_id;
            $data->produit =  env("TOUPESU_PRODUCT");

            $token = self::getToupesuToken();

            $result = GeneralHelper::postTo(env("TOUPESU_ROOT_URL") .'/api/disbursement/status',$data,$token);
        }



        if($result->is_success && $result->result->success){

            if($result->result->status=="FAILED"){

                $achat->requestable->status = PaymentStatus::FAILED;
                $achat->status = PaymentStatus::FAILED;
                PayOutFailureEvent::dispatch($achat);

            }elseif ($result->result->status=="COMPLETED"){
                // Successful payment
                $achat->requestable->status = PaymentStatus::SUCCESSFUL;
                $achat->status = PaymentStatus::SUCCESSFUL;


            }else{

                $achat->status = PaymentStatus::PENDING;
                $achat->requestable->status = PaymentStatus::PENDING;

            }

            $achat->save();
            return [
                "pay_token"=> $achat->ref_id,
                "amount"=> -1 * $achat->amount,
                "status"=>$achat->status,
                "ref_id"=> $achat->user_ref_id,
                "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
            ];
        }


        return [
            "pay_token"=> $achat->ref_id,
            "amount"=> -1 * $achat->amount,
            "status"=>$achat->status,
            "ref_id"=> $achat->user_ref_id,
            "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
        ];


    }


    /**
     * @return null
     * @throws GuzzleException
     */

    public static function getToupesuToken() {
        return GeneralHelper::getAccessToken(ToupesuAccessToken::class, env("TOUPESU_CLIENT_ID"), env("TOUPESU_CLIENT_SECRET"), env("TOUPESU_ROOT_URL"));
    }


    public static function generateMomentTime(): string
    {

        return "Toupesu-". parent::UUID();

    }

}
