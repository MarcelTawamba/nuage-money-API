<?php

namespace App\Jobs;

use App\Models\StartButtonBank;
use App\Services\StartButtonAfricaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetStartButtonBanksToBDJob implements ShouldQueue
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
        $starButton = new StartButtonAfricaService();
        $currency = "NGN";
        $banksRes = $starButton->getListOfBanks($currency);
        if(!$banksRes["success"]) {
            return;;
        }

        $banks = $banksRes["data"];

        foreach ($banks as $bank) {
            $starbuttonBank = null;

            if(isset($bank->id)) {
                $starbuttonBank = StartButtonBank::where("startbutton_id", "=", $bank->id)->first();
            }

            if(!$starbuttonBank) {
                $starbuttonBank = new StartButtonBank();
            }

            $starbuttonBank->name = $bank->name;
            $starbuttonBank->code = $bank->code;
            $starbuttonBank->currency = $currency;
            $starbuttonBank->startbutton_id = $bank->id;
            $starbuttonBank->save();
        }

        echo "Done\n";
    }
}
