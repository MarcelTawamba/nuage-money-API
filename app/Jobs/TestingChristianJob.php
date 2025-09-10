<?php

namespace App\Jobs;

use App\Classes\ConvertionHelper;
use App\Services\Fincra\FincraService;
use App\Services\StartButtonAfricaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class TestingChristianJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $fincraService = new FincraService();

        info("Fincra Bank",["data"=>$fincraService->getBusinessId()]);

        //$starButton = new StartButtonAfricaService();
        //print_r($starButton->getListOfBanks());
        //print_r($starButton->requestPayment(5000, "12345678915"));
        //print_r($starButton->bankAccountValidation("144", "0066259148"));
        //print_r($starButton->makeTransfert(2500, "044", "0066259148", "12345678909876544"));
        //print_r($starButton->checkTransaction("12345678912"));

        //print_r(ConvertionHelper::convertWithTo(604223, "USD", "XAF", 5) );
    }
}
