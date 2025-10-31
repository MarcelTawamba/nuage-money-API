<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\Achat;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\StartButton\PayInRequest;
use App\Models\PayOutRequest;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Services\StartButton\AfricaService;
use libphonenumber\NumberParseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
     * initiate a PAY-IN or COLLECTION
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
        $webhookUrl = url('/api/startbutton-callback');
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
            $startButtonAfricaService = new  AfricaService();

            $result = $startButtonAfricaService->requestPayment($new_achat->amount*100 ,$new_achat->ref_id,strtoupper($new_achat->currency) , $input["email"], $redirectUrl, $webhookUrl, $validatedPaymentMethods, $metadata);
        }

        if($result["success"]){
                $new_start_button_request = new PayInRequest();
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

        Log::channel("slack")->info("Error when making pay-in", [
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

            $startButtonAfricaService = new  AfricaService();

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

            $startButtonAfricaService = new  AfricaService();

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

    static public function initPayout(array $input): JsonResponse
    {
        $client = Client::firstOrCreate(
            ['id' => $input['client_id']],
            [
                'user_id' => $input['client_id'],
                'name' => $input['first_name'] . ' ' . $input['last_name'],
                'company_id' => $input['company_id']
            ]
        );

        $clientWallet = ClientWallet::firstOrCreate(['client_id' => $client->id]);

        $walletType = WalletType::firstOrCreate(['name' => $input['currency']], ['decimals' => 0]);

        $wallet = Wallet::firstOrNew(
            [
                'user_type' => ClientWallet::class,
                'user_id' => $clientWallet->id,
                'wallet_type_id' => $walletType->id,
            ]
        );

        if (!$wallet->exists) {
            $wallet->balance = $input['account_balance'];
            $wallet->save();
        }

        /**** Create a new Achat object for this user request */
        $new_achat = new Achat();
        $new_achat->client_id = $input['client_id'];
        $new_achat->amount = -1 * $input["amount"];
        $new_achat->country = $input["country"];
        $new_achat->currency = $input["currency"];
        $new_achat->user_ref_id = $input["ref_id"];
        $new_achat->ref_id = self::generateMomentTime();

        // Prepare payload for the transfer API
        $payoutData = [
            'amount' => $input['amount'],
            'currency' => strtoupper($new_achat->currency),
            'reference' => $new_achat->user_ref_id,
            'country' => $new_achat->country,
        ];
        $paymentMethod = '';

        if (env("NUAGE_ENV", "SANDBOX") == "SANDBOX") {
            $result = [
                "success" => true,
                "message" => "transfer",
                "data" => "processing"
            ];
        } else {
            $startButtonAfricaService = new AfricaService();

            // Add bank or mobile money details
            if (!empty($input['metadata']) &&
                !empty($input['metadata']['bank_code']) &&
                !empty($input['metadata']['dest_account_number'])) {
                $account = self::verifyAccount($input);

                if (!$account["success"]) {
                    return response()->json($account);
                }
                $payoutData['bankCode'] = $input['metadata']['bank_code'];
                $payoutData['accountNumber'] = $input['metadata']['dest_account_number'];
                $paymentMethod = self::getPaymentMethodEnum('bank');
            } elseif (!empty($input['metadata']['MNO']) &&
                !empty($input['metadata']['msisdn'])) {
                $payoutData['MNO'] = $input['metadata']['MNO'];
                $payoutData['msisdn'] = $input['metadata']['msisdn'];
                $paymentMethod = self::getPaymentMethodEnum('mobile_money');
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Missing required bank or mobile money details for payout."
                ]);
            }

            $payoutData['webhookUrl'] = url('/api/startbutton-callback');

            // check that the StartButton available balance for the given currency is > amount
            $walletBalanceResponse = $startButtonAfricaService->getWalletBalance();
            Log::info('Wallet Balance Response: ', ['walletBalanceResponse' => $walletBalanceResponse]);
            $sbBalance  = 0;
            $result = null;
            if ($walletBalanceResponse["success"]) {
                foreach ($walletBalanceResponse['data'] as $wallet) {
                    if (isset($wallet['currency'])) {
                        WalletType::updateOrCreate(
                            ['name' => $wallet['currency']],
                            ['decimals' => 0]
                        );
                    }
                    if (isset($wallet['currency']) && $wallet['currency'] === $payoutData["currency"]) {
                        $sbBalance = $wallet['availableBalance'];
                    }
                }

                if ($sbBalance >= $payoutData["amount"]) {
                    Log::info('MakeTrasnfer Request Payload: ', ['payoutData' => $payoutData]);
                    $result = $startButtonAfricaService->makeTransfer($payoutData);
                    Log::info('MakeTrasnfer Response: ', ['Response' => $result]);
                } else {
                    Log::channel("slack")->info("Insufficient funds for making payout", [
                        "walletBalance" => $walletBalanceResponse,
                        "amountToPayout" => $payoutData["amount"]
                    ]);
                }
            } else {
                Log::channel("slack")->info("Cannot fetch wallet balance prior to making payout transfer.");
            }

        }

        if ($result["success"]) {
            /**** save the new PayOutRequest object **/
            $new_pay_out_request = new PayOutRequest();
            $new_pay_out_request->service = $input['service'];
            $new_pay_out_request->account_name = $input['user_name'];
            $new_pay_out_request->account_number = $input['metadata']["dest_account_number"];
            $new_pay_out_request->status = PaymentStatus::CREATED;
            $new_pay_out_request->bank_code = $input['metadata']["bank_code"] ?? null; // is null for mobile money
            $new_pay_out_request->mno = $input["MNO"] ?? null;
            $new_pay_out_request->msisdn = $input["msisdn"] ?? null;

            $new_pay_out_request->save();

            $new_achat->requestable()->associate($new_pay_out_request);
            $new_achat->status = PaymentStatus::CREATED;
            $new_achat->save();

            Log::info('saving the transaction: ', ['Transaction' => $new_achat]);
            self::saveTransaction($new_achat);

            CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(5));

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
        Log::channel("slack")->info("Error when making payout", [
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

            $startButtonAfricaService = new  AfricaService();

            // TODO: get country code
            $account = $startButtonAfricaService->bankAccountValidation($input["bank_code"],$input["account_number"],$input["country"]);

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
