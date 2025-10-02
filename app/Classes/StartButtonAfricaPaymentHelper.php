<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\Achat;
use App\Models\StartButtonPayInRequest;
use App\Models\StartButtonPayOutRequest;
use App\Services\StartButtonAfricaService;
use libphonenumber\NumberParseException;
use Illuminate\Http\JsonResponse;
use function Symfony\Component\Translation\t;

class StartButtonAfricaPaymentHelper extends GeneralPaymentHelper
{
    private static $availableMethodsByCurrency = [
        'NGN' => ['bank', 'card', 'bank_transfer', 'ussd', 'payattitude'],
        'GHS' => ['card', 'mobile_money'],
        'ZAR' => ['eft', 'qr', 'card'],
        'KES' => ['mobile_money', 'card'],
        'UGX' => ['mobile_money', 'card'],
        'RWF' => ['mobile_money', 'card'],
        'XOF' => ['mobile_money', 'card'],
        'XAF' => ['mobile_money', 'card'],
    ];

    private static function getPaymentMethodEnum(string $method): string
    {
        return match ($method) {
            'mobile_money' => PaymentMethod::START_BUTTON_MOBILE,
            'card' => PaymentMethod::START_BUTTON_CARD,
            'bank' => PaymentMethod::START_BUTTON_BANK,
            'bank_transfer' => PaymentMethod::START_BUTTON_BANK_TRANSFER,
            'ussd' => PaymentMethod::START_BUTTON_USSD,
            'payattitude' => PaymentMethod::START_BUTTON_PAYATTITUDE,
            'eft' => PaymentMethod::START_BUTTON_EFT,
            'qr' => PaymentMethod::START_BUTTON_QR,
            default => PaymentMethod::START_BUTTON,
        };
    }

    private static function getValidatedPaymentMethods(string $currency, array $requestedMethods): array
    {
        if (empty($requestedMethods)) {
            return [self::$availableMethodsByCurrency[$currency][0]];
        }

        $validatedMethods = [];
        foreach ($requestedMethods as $method) {
            if (in_array($method, self::$availableMethodsByCurrency[$currency])) {
                $validatedMethods[] = $method;
            } else {
                return [];
            }
        }

        return $validatedMethods;
    }

    /**
     * @throws NumberParseException
     */
    static public function initPayment(array $input): JsonResponse
    {
        $req = Achat::where("user_ref_id",$input["ref_id"])->where("client_id",$input["service"])->first();

        if($req instanceof   Achat){
            return response()->json([
                "success"=> false,
                "message"=>"Duplicate ref_id"
            ]);
        }

        $new_achat = new  Achat();
        $new_achat->client_id = $input["service"];
        $new_achat->amount = $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["ref_id"];
        $new_achat->ref_id = self::generateMomentTime();

        $redirectUrl = $input['redirectUrl'] ?? null;
        $webhookUrl = $input['webhookUrl'] ?? null;
        $paymentMethods = $input['paymentMethods'] ?? null;
        $metadata = $input['metadata'] ?? [];

        if ($paymentMethods) {
            $validatedPaymentMethods = self::getValidatedPaymentMethods($new_achat->currency, $paymentMethods);
            if (empty($validatedPaymentMethods)) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid payment methods for the given currency."
                ]);
            }
        } else {
            $validatedPaymentMethods = self::getValidatedPaymentMethods($new_achat->currency, []);
        }

        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){
            $result = [
                "data" => "http://pay.startbutton.builditdigital.co.s3-website-eu-west-1.amazonaws.com/#/uswfao9b4v",
                "success"=>true
            ];
        }else{
            $startButtonAfricaService = new  StartButtonAfricaService();

            $result = $startButtonAfricaService->requestPayment($new_achat->amount*100 ,$new_achat->ref_id,strtoupper($new_achat->currency) , $input["email"], $redirectUrl, $webhookUrl, $validatedPaymentMethods, $metadata);
        }

        if($result["success"]){
                $new_start_button_request = new StartButtonPayInRequest();
                $new_start_button_request->email = $input['email'];
                $new_start_button_request->payment_link = $result["data"];
                $new_start_button_request->status = PaymentStatus::CREATED;
                $new_start_button_request->redirect_url = $redirectUrl;
                $new_start_button_request->webhook_url = $webhookUrl;
                $new_start_button_request->payment_methods = $validatedPaymentMethods;
                $new_start_button_request->metadata = $metadata;

                $new_start_button_request->save();

                $new_achat->requestable()->associate( $new_start_button_request);
                $new_achat->status= PaymentStatus::CREATED;
                $new_achat->save();

                CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(40));

                $paymentMethod = self::getPaymentMethodEnum($validatedPaymentMethods[0]);

                /*** return a json respond when request created ***/
                return response()->json([
                    "pay_token"=> $new_achat->ref_id,
                    "amount"=> $new_achat->amount,
                    "ref_id"=> $new_achat->user_ref_id,
                    "payment_link"=> $new_start_button_request->payment_link,
                    "payment_method"=> $paymentMethod,
                    "status"=>$new_achat->status,
                    "success"=>true,
                ]);
        }

        \Log::channel("slack")->info("Error when making pay-in", [
            "Data" => $result
        ]);
        /*** return a json respond when request errors  **/
        return response()->json([
            "success"=>false,
            "pay_token"=> $new_achat->ref_id,
            "ref_id"=> $new_achat->user_ref_id,
            "amount"=> $new_achat->amount,
            "status"=> PaymentStatus::FAILED,
            "message"=> "Request has failed try latter"
        ]);
    }

    static public function checkRequestPayments(Achat $achat): array
    {

        if(env("NUAGE_ENV","SANDBOX") == "SANDBOX"){


            $trans = new \stdClass();
            $trans->status = PaymentStatus::SUCCESSFUL;

            $data = new \stdClass();
            $data->transaction = $trans;

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
        \Log::channel("slack")->info("StartButtonWebHookController Data is OK and recevied", [
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

    static public function initPayout(array $input): JsonResponse
    {
        /**** Create a new Achat object for this user request */
        $new_achat = new Achat();
        $new_achat->client_id = $input["service"];
        $new_achat->amount = -1 * $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["ref_id"];
        $new_achat->ref_id = self::generateMomentTime();

        if (env("NUAGE_ENV", "SANDBOX") == "SANDBOX") {
            $result = [
                "success" => true,
                "message" => "transfer",
                "data" => "processing"
            ];
        } else {
            $startButtonAfricaService = new StartButtonAfricaService();
            $account = self::verifyAccount($input);

            if (!$account["success"]) {
                return response()->json($account);
            }

            // Prepare payload for the transfer API
            $payoutData = [
                'amount' => $input['amount'],
                'currency' => strtoupper($new_achat->currency),
                'reference' => $new_achat->ref_id,
                'country' => $new_achat->country,
            ];

            // Add bank or mobile money details
            if (!empty($input['bank_code']) && !empty($input['account_number'])) {
                $payoutData['bankCode'] = $input['bank_code'];
                $payoutData['accountNumber'] = $input['account_number'];
                $paymentMethod = self::getPaymentMethodEnum('bank');
            } elseif (!empty($input['MNO']) && !empty($input['msisdn'])) {
                $payoutData['MNO'] = $input['MNO'];
                $payoutData['msisdn'] = $input['msisdn'];
                $paymentMethod = self::getPaymentMethodEnum('mobile_money');
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Missing required bank or mobile money details for payout."
                ]);
            }

            // Add optional webhookUrl
            if (!empty($input['webhook_url'])) {
                $payoutData['webhookUrl'] = $input['webhook_url'];
            }

            $result = $startButtonAfricaService->makeTransfer($payoutData);
        }

        if ($result["success"]) {
            /**** save the new StartButtonPayOutRequest object when request created **/
            $new_start_button_request = new StartButtonPayOutRequest();
            $new_start_button_request->account_name = $input["account_name"];
            $new_start_button_request->account_number = $input["account_number"];
            $new_start_button_request->status = PaymentStatus::CREATED;
            $new_start_button_request->bank_code = $input["bank_code"] ?? null; // Can be null for mobile money
            $new_start_button_request->mno = $input["MNO"] ?? null;
            $new_start_button_request->msisdn = $input["msisdn"] ?? null;

            $new_start_button_request->save();

            $new_achat->requestable()->associate($new_start_button_request);
            $new_achat->status = PaymentStatus::CREATED;
            $new_achat->save();

            self::saveTransaction($new_achat);

            CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(20));

            /*** return a json respond when request created ***/
            return response()->json([
                "pay_token" => $new_achat->ref_id,
                "amount" => -1 * $new_achat->amount,
                "ref_id" => $new_achat->user_ref_id,
                "payment_method" => $paymentMethod,
                "status" => $new_achat->status,
                "success" => true
            ]);
        }

        /*** return a json respond when request errors  **/
        \Log::channel("slack")->info("Error when making payout", [
            "Data" => $result
        ]);
        return response()->json([
            "pay_token" => $new_achat->ref_id,
            "ref_id" => $new_achat->user_ref_id,
            "amount" => -1 * $new_achat->amount,
            "status" => PaymentStatus::FAILED,
            "message" => "Payment has failed try latter",
            "success" => false
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