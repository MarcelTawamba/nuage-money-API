<?php

namespace App\Classes;

use App\Enums\FincraType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\CheckToupesuRequestStatus;
use App\Models\FincraMobilePaymentRequest;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class FincraPaymentHelper
{

    /**
     * @throws \Exception
     * @throws GuzzleException
     */


    public function initPayment($input): \Illuminate\Http\JsonResponse
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
        $request->ref_id = $this->generateMomentTime(4);

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

    public static function initPayout(array $input): \Illuminate\Http\JsonResponse
    {
        Log::info('Initiating Fincra payout');

        // Setup user, client, and wallet
        $setup = GeneralPaymentHelper::setupUserAndWallet($input);
        $client = $setup['client'];
        $wallet = $setup['wallet'];

        // Create Achat record
        $new_achat = GeneralPaymentHelper::createAchatForPayout($client->id, $input, 'FINCRA-');

        if (env('NUAGE_ENV', 'SANDBOX') == 'SANDBOX') {
            $result = [
                'success' => true,
                'message' => 'Fincra payout initiated (sandbox)',
                'data' => 'processing',
            ];
        } else {
            $fincraService = new \App\Services\Fincra\FincraService();

            try {
                // Check if metadata contains bank or mobile money details
                if (!empty($input['metadata']['bank_code']) && 
                    !empty($input['metadata']['dest_account_number'])) {
                    
                    // Bank account payout
                    $payoutData = [
                        'sourceCurrency' => 'NGN', // Or get from wallet
                        'destinationCurrency' => strtoupper($input['currency']),
                        'amount' => $input['amount'],
                        'description' => 'Payout from Nuage',
                        'customerReference' => $new_achat->ref_id,
                        'beneficiary' => [
                            'firstName' => $input['first_name'] ?? 'Customer',
                            'lastName' => $input['last_name'] ?? '',
                            'type' => 'individual',
                            'accountHolderName' => $input['metadata']['dest_account_name'] ?? $input['user_name'],
                            'accountNumber' => $input['metadata']['dest_account_number'],
                            'bankCode' => $input['metadata']['bank_code']
                        ],
                        'paymentDestination' => 'bank_account'
                    ];

                    $payoutResponse = $fincraService->createPayout($payoutData);
                    
                    $result = [
                        'success' => true,
                        'message' => 'Fincra bank payout initiated',
                        'data' => $payoutResponse,
                    ];
                    
                } elseif (!empty($input['metadata']['MNO']) && 
                          !empty($input['metadata']['msisdn'])) {
                    
                    // Mobile money payout
                    $payoutData = [
                        'sourceCurrency' => 'NGN',
                        'destinationCurrency' => strtoupper($input['currency']),
                        'amount' => $input['amount'],
                        'description' => 'Payout from Nuage',
                        'customerReference' => $new_achat->ref_id,
                        'beneficiary' => [
                            'firstName' => $input['first_name'] ?? 'Customer',
                            'lastName' => $input['last_name'] ?? '',
                            'type' => 'individual',
                            'mobileMoneyCode' => $input['metadata']['MNO'],
                            'phoneNumber' => $input['metadata']['msisdn']
                        ],
                        'paymentDestination' => 'mobile_money_wallet'
                    ];

                    $payoutResponse = $fincraService->createPayout($payoutData);
                    
                    $result = [
                        'success' => true,
                        'message' => 'Fincra mobile money payout initiated',
                        'data' => $payoutResponse,
                    ];
                    
                } else {
                    $result = [
                        'success' => false,
                        'message' => 'Missing required bank or mobile money details for Fincra payout',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Fincra payout error', ['error' => $e->getMessage()]);
                $result = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($result['success']) {
            $new_pay_out_request = new \App\Models\PayOutRequest;
            $new_pay_out_request->service = PaymentMethod::FINCRA;
            $new_pay_out_request->account_name = $input['user_name'];
            $new_pay_out_request->account_number = $input['metadata']['dest_account_number'] 
                ?? $input['metadata']['msisdn'] 
                ?? null;
            $new_pay_out_request->status = PaymentStatus::CREATED;
            $new_pay_out_request->bank_code = $input['metadata']['bank_code'] ?? null;
            $new_pay_out_request->mno = $input['metadata']['MNO'] ?? null;
            $new_pay_out_request->msisdn = $input['metadata']['msisdn'] ?? null;
            $new_pay_out_request->save();

            $new_achat->requestable()->associate($new_pay_out_request);
            $new_achat->status = PaymentStatus::CREATED;
            $new_achat->save();

            return response()->json([
                'pay_token' => $new_achat->ref_id,
                'amount' => -1 * $new_achat->amount,
                'ref_id' => $new_achat->user_ref_id,
                'payment_method' => PaymentMethod::FINCRA,
                'status' => $new_achat->status,
                'success' => true,
            ]);
        }

        return response()->json([
            'pay_token' => $new_achat->ref_id,
            'ref_id' => $new_achat->user_ref_id,
            'amount' => -1 * $new_achat->amount,
            'status' => PaymentStatus::FAILED,
            'message' => $result['message'] ?? 'Payment has failed',
            'success' => false,
        ]);
    }
    
    public function generateMomentTime(int $lgt = 4): string
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
