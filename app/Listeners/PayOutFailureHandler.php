<?php

namespace App\Listeners;

use App\Enums\PaymentStatus;
use App\Events\PayOutFailureEvent;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\SystemLedger;
use App\Models\Transaction;
use App\Models\Wallet;
use CoreProc\WalletPlus\Models\WalletType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PayOutFailureHandler implements  ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the eventa
     */
    public function handle(PayOutFailureEvent $event): void
    {
        $currency= WalletType::where("name",$event->achat->currency)->first();

        $client = Client::find($event->achat->client_id);


        $wallet = Wallet::where('user_type',ClientWallet::class)->where('user_id',$client->wallet->id)->where('wallet_type_id', $currency->id)->first();

        $system_wallet =  Wallet::where('user_type',SystemLedger::class)->where('user_id',SystemLedger::whereName("system")->first()->id)->where('wallet_type_id', $currency->id)->first();
        $system_fee_wallet =  Wallet::where('user_type',SystemLedger::class)->where('user_id',SystemLedger::whereName("system fee")->first()->id)->where('wallet_type_id', $currency->id)->first();


        $transaction = Transaction::where("reference",$event->achat->ref_id)->where("wallet_id",$wallet->id)->where("amount",$event->achat->amount)->where("refund",false)->first();

        if(($event->achat->status == PaymentStatus::FAILED) && ($transaction instanceof  Transaction)){
            try {
                \Illuminate\Support\Facades\DB::transaction(function() use ($event,$transaction,$wallet,$system_wallet,$system_fee_wallet,) {

                    $transactions = Transaction::where("reference",$event->achat->ref_id)->get();

                    $fee = 0;

                    foreach ( $transactions as $trans){

                        $trans->refund = true;
                        $trans->save();

                        if($trans->wallet_id == $system_fee_wallet->id){
                            $fee = $trans->amount;
                        }
                    }

                    if ($fee == 0){
                        throw new  \Exception("fees is zero");
                    }




                    // Transaction to fund client wallet
                    $new_transaction = new Transaction();
                    $new_transaction->reference = $event->achat->ref_id;
                    $new_transaction->amount = -1 * $transaction->amount;
                    $new_transaction->wallet_id = $wallet->id;
                    $new_transaction->balance_after = $wallet->balance + (-1 * $transaction->amount);
                    $new_transaction->balance_before = $wallet->balance ;
                    $new_transaction->achatable()->associate($event->achat);
                    $new_transaction->description = "Refund user wallet from  ".$event->achat->ref_id;
                    $new_transaction->refund = true;
                    $new_transaction->save();
                    $wallet->incrementBalance((-1 * $transaction->amount));

                    // Transaction to fund system wallet
                    $new_transaction_to_system = new Transaction();
                    $new_transaction_to_system->reference = $event->achat->ref_id;
                    $new_transaction_to_system->amount = (-1 * $transaction->amount);
                    $new_transaction_to_system->wallet_id = $system_wallet->id;
                    $new_transaction_to_system->balance_after = $system_wallet->balance + (-1 * $transaction->amount);
                    $new_transaction_to_system->balance_before = $system_wallet->balance ;
                    $new_transaction_to_system->achatable()->associate($event->achat);
                    $new_transaction_to_system->description = "Refund System from  ".$event->achat->ref_id;
                    $new_transaction_to_system->refund = true;
                    $new_transaction_to_system->save();
                    $system_wallet->incrementBalance((-1 * $transaction->amount));

                    // Transaction to collect fee from client wallet
                    $new_transaction_get_system_fee_from_wallet = new Transaction();
                    $new_transaction_get_system_fee_from_wallet->reference = $event->achat->ref_id;
                    $new_transaction_get_system_fee_from_wallet->amount =  -1 * $fee;
                    $new_transaction_get_system_fee_from_wallet->wallet_id = $system_fee_wallet->id;
                    $new_transaction_get_system_fee_from_wallet->balance_after = $system_fee_wallet->balance - $fee;
                    $new_transaction_get_system_fee_from_wallet->balance_before = $system_fee_wallet->balance ;
                    $new_transaction_get_system_fee_from_wallet->achatable()->associate($new_transaction);
                    $new_transaction_get_system_fee_from_wallet->description = "Fees collected for Refund  for transaction ".$new_transaction->reference;
                    $new_transaction_get_system_fee_from_wallet->refund = true;
                    $new_transaction_get_system_fee_from_wallet->save();
                    $system_fee_wallet->decrementBalance($fee);

                    // Transaction to fund system fee wallet
                    $new_transaction_to_system_fee = new Transaction();
                    $new_transaction_to_system_fee->reference = $event->achat->ref_id;
                    $new_transaction_to_system_fee->amount = $fee;
                    $new_transaction_to_system_fee->wallet_id = $wallet->id;
                    $new_transaction_to_system_fee->balance_after = $wallet->balance + $fee;
                    $new_transaction_to_system_fee->balance_before = $wallet->balance ;
                    $new_transaction_to_system_fee->achatable()->associate($new_transaction_get_system_fee_from_wallet);
                    $new_transaction_to_system_fee->description = "Fees Refund for the transaction ".$new_transaction->reference;
                    $new_transaction_to_system_fee->refund = true;
                    $new_transaction_to_system_fee->save();
                    $wallet->incrementBalance($fee);


                });
            }catch (\Exception|\Throwable $e) {

                info("Error has occur",["data"=>$event->achat,"error"=>$e]);


            }
        }


    }
}
