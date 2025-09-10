<?php

namespace App\Http\Controllers\API;

use App\Enums\PaymentStatus;
use App\Events\PayOutFailureEvent;
use App\Http\Controllers\Controller;
use App\Models\Achat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StartButtonWebHookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        \Log::channel("slack")->info("StartButtonWebHookController called", [
            "Req" => $request
        ]);

        $secret = env('STARTBUTTON_SECRET_KEY');
        $requestBody = $request->getContent();
        $signature = $request->header('x-startbutton-signature');

        if ($this->isValidSignature($secret, $requestBody, $signature)) {
            // Request is valid, process the event
            $result = json_decode($requestBody, true);
            // Do something with the event data
            \Log::channel("slack")->info("StartButtonWebHookController Data is OK and recevied", [
                "Data" => $result
            ]);

            if ( $result["success"]){
                $achat = Achat::whereRefId($result["data"]->transaction->userTransactionReference)->first() ;
                if($achat instanceof Achat){

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
                }
            }

        }

        return response()->json([], Response::HTTP_OK);
    }

    private function isValidSignature($secret, $data, $signature)
    {
        $calculatedSignature = hash_hmac('sha512', $data, $secret);
        return hash_equals($calculatedSignature, $signature);
    }
}
