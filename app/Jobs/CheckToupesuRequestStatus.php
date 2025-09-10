<?php

namespace App\Jobs;

use App\Classes\StartButtonAfricaPaymentHelper;
use App\Classes\ToupesuGeneralPaymentHelpers;
use App\Enums\PaymentStatus;
use App\Models\Achat;
use App\Models\StartButtonPayInRequest;
use App\Models\StartButtonPayOutRequest;
use App\Models\ToupesuPaymentRequest;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckToupesuRequestStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     public Achat $achat;
    /**
     * Create a new job instance.
     */
    public function __construct(Achat $achat)
    {
        //
        $this->achat = $achat;
    }

    /**
     * Execute the job.
     * @throws GuzzleException
     */
    public function handle(): void
    {


        if($this->achat->status == PaymentStatus::CREATED || $this->achat->status == PaymentStatus::PENDING){


            if($this->achat->requestable_type == ToupesuPaymentRequest::class){
                if($this->achat->amount >0){
                    $result = ToupesuGeneralPaymentHelpers::checkRequestPayments($this->achat);
                }else{
                    $result = ToupesuGeneralPaymentHelpers::checkRequestPayout($this->achat);
                }

            }elseif($this->achat->requestable_type == StartButtonPayInRequest::class){

                $result = StartButtonAfricaPaymentHelper::checkRequestPayments($this->achat);


            }elseif($this->achat->requestable_type == StartButtonPayOutRequest::class){

                $result = StartButtonAfricaPaymentHelper::checkRequestPayout($this->achat);
            }


            $new_achat = Achat::find($this->achat->id);

            $new_achat->job_tries += 1;
            $new_achat->save();



            if(($result["status"] == PaymentStatus::PENDING || $result["status"] == PaymentStatus::CREATED) && $new_achat->job_tries < 20){

                CheckToupesuRequestStatus::dispatch($new_achat)->delay(now()->addSeconds(20));

            }
        }

    }
}
