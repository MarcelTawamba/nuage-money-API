<?php

namespace App\Classes;

use App\Enums\FincraType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\FincraMobilePaymentRequest;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class FincraPaymentHelper
{

    /**
     * @throws \Exception
     * @throws GuzzleException
     */


    static public function initPayment($input): \Illuminate\Http\JsonResponse
    {
        /**** check if ref_id exist for this service **/
        $req = FincraMobilePaymentRequest::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->first();

        if($req instanceof  FincraMobilePaymentRequest){
            return response()->json([
                "pay_token"=> $req->ref_id,
                "amount"=> $req->amount,
                "status"=>$req->status,
                "ref_id"=> $req->user_ref_id,
                "payment_method"=> PaymentMethod::FINCRA,
                "success"=> true
            ]);
        }

        /**** Create a new ToupesuPaymentRequest object for this user request */

        $request = new  FincraMobilePaymentRequest();
        $request->msidn = $input["msidn"];
        $request->client_id = $input["service"];
        $request->amount = $input["amount"];
        $request->country_code = $input["country"];
        $request->currency_code = $input["currency"];
        $request->user_ref_id = $input["ref_id"];
        $request->ref_id = self::generateMomentTime(4);

        /*** toupesu mobile payment data  */

        $data = new  \stdClass();
         //"card":{"card_number":"5319317801366660","cvv":"000","expiry_month":"10","expiry_year":"26"},"phone":"+234 0909090"}
        $data->phone = $request->msidn;
        $data->amount = $request->amount;
        $data->currency = $request->currency_code ;
        $data->reference = $request->ref_id;
        $data->type = FincraType::MOBILE_MONEY;
        $data->customer = new  \stdClass();
        $data->customer->name = "";
        $data->customer->email = "";
        $data->customer->phone = "";





        /*** post the request to toupesu ***/
        $result = GeneralHelper::postTo(env("FINCRA_ROOT_URL") .'/api/main/reqPayment',$data,null, env("FINCRA_API_KEY"));


        if($result->is_success  ){

            if($result->result->status  && $result->result->data->status == "pending " ){
                /**** save the new ToupesuPaymentRequest object when request created **/
                $request->pay_token = $result->result->data->id;
                $request->status = PaymentStatus::CREATED;
                $request->payment_method = $result->result->data->metadata->operator;

                $request->save();

                CheckToupesuRequestStatus::dispatch($request)->delay(now()->addSeconds(20));

                /*** return a json respond when request created ***/
                return response()->json([
                    "pay_token"=> $request->ref_id,
                    "amount"=> $request->amount,
                    "ref_id"=> $request->user_ref_id,
                    "payment_method"=> PaymentMethod::TOUPESU_MOBILE,
                    "status"=>$request->status,

                ]);

            }


        }

        /*** return a json respond when request errors  **/
        return response()->json([
            "pay_token"=> $request->ref_id,
            "ref_id"=> $request->user_ref_id,
            "amount"=> $request->amount,
            "status"=> PaymentStatus::FAILED,
        ]);


    }



    public static function generateMomentTime(int $lgt = 4): string
    {
        $date = Carbon::now();
        $year = $date->year;
        $month = $date->month;
        $day = $date->day;
        $hour = $date->hour;
        $minutes = $date->minute;
        $secondes = $date->second;
        $momentTime = $year . $month . $day . $hour . $minutes . $secondes;

        if($lgt) {
            $momentTime = $momentTime . Str::random($lgt);
        }
        return "Fincra".$momentTime;
    }


}
