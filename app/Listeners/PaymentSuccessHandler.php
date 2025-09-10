<?php

namespace App\Listeners;
use App\Models\AdminDepositeRequest;
use App\Models\ClientWallet;
use App\Models\CustomFee;
use App\Models\ExchangeRateMargin;
use App\Models\ExchangeRequest;
use App\Models\SystemLedger;
use CoreProc\WalletPlus\Models\WalletType;
use App\Models\Wallet;
use App\Enums\PaymentStatus;
use App\Enums\PayType;
use App\Events\PaymentSuccessEvent;
use App\Models\Client;
use App\Models\CountryAvaillable;
use App\Models\Operator;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentSuccessHandler implements  ShouldQueue
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
     * Handle the event.
     */
    public function handle(PaymentSuccessEvent $event): void
    {

        $transaction = Transaction::where("reference",$event->achat->ref_id)->first();

        if(($event->achat->status == PaymentStatus::SUCCESSFUL) && !($transaction instanceof  Transaction)){
            try {
                \Illuminate\Support\Facades\DB::transaction(function() use ($event) {

                    $currency= WalletType::where("name",$event->achat->currency)->first();

                    $client = Client::find($event->achat->client_id);
                    $client_wallet  = $client->wallet;
                    if( !($client_wallet instanceof ClientWallet)){
                        $client_wallet = new  ClientWallet();
                        $client_wallet->client_id = $client->id;
                        $client_wallet->save();

                    }

                    $wallet = Wallet::where('user_type',ClientWallet::class)->where('user_id',$client->wallet->id)->where('wallet_type_id', $currency->id)->first();

                    $system_wallet =  Wallet::where('user_type',SystemLedger::class)->where('user_id',SystemLedger::whereName("system")->first()->id)->where('wallet_type_id', $currency->id)->first();
                    $system_fee_wallet =  Wallet::where('user_type',SystemLedger::class)->where('user_id',SystemLedger::whereName("system fee")->first()->id)->where('wallet_type_id', $currency->id)->first();


                    if( ! ($system_wallet instanceof Wallet)){


                        $system = SystemLedger::whereName("system")->first();

                        $system_wallet = new  Wallet();

                        $system_wallet->user()->associate($system);
                        $system_wallet->wallet_type_id = $currency->id;
                        $system_wallet->raw_balance = 0;
                        $system_wallet->save();


                    }

                    if( ! ($system_fee_wallet instanceof Wallet)){

                        $system_fee  = SystemLedger::whereName("system fee")->first();
                        $system_fee_wallet = new  Wallet();

                        $system_fee_wallet->user()->associate($system_fee);
                        $system_fee_wallet->wallet_type_id = $currency->id;
                        $system_fee_wallet->raw_balance = 0;
                        $system_fee_wallet->save();
                    }


                    if( ! ($wallet instanceof Wallet)){


                        $wallet = new  Wallet();

                        $wallet->user()->associate($client_wallet );
                        $wallet->wallet_type_id = $currency->id;
                        $wallet->raw_balance = 0;
                        $wallet->save();
                    }

                    $country = CountryAvaillable::where("code",strtoupper($event->achat->country) )->first();

                    if($event->achat->requestable_type == AdminDepositeRequest::class || $event->achat->requestable_type == ExchangeRequest::class){
                        $total_fee_amount = 0;

                    }else{
                        $fees =  Operator::where("currency_id",$currency->id)->where("country_id",$country->id)->where("type",PayType::PAY_IN)->first();
                        $custom_fee = CustomFee::where("company_id",$client->company_id)->where("method_id",$fees->id)->first();

                        if($custom_fee instanceof  CustomFee){
                            $fees = $custom_fee;
                        }


                        $total_fee_amount = $fees->fee_type == "percentage" ? $event->achat->amount * ( $fees->fees / 100 ) : $fees->fees;
                    }


                    $total_fee_amount = round($total_fee_amount,2);


                    // Transaction to fund client wallet
                    $new_transaction = new Transaction();
                    $new_transaction->reference = $event->achat->ref_id;
                    $new_transaction->amount = $event->achat->amount;
                    $new_transaction->wallet_id = $wallet->id;
                    $new_transaction->balance_after = $wallet->balance + $event->achat->amount;
                    $new_transaction->balance_before = $wallet->balance ;
                    $new_transaction->achatable()->associate($event->achat);
                    $new_transaction->description = "Cash in from ".$event->achat->ref_id;
                    $new_transaction->save();
                    $wallet->incrementBalance($event->achat->amount);

                    // Transaction to fund system wallet
                    $new_transaction_to_system = new Transaction();
                    $new_transaction_to_system->reference = $event->achat->ref_id;
                    $new_transaction_to_system->amount = $event->achat->amount;
                    $new_transaction_to_system->wallet_id = $system_wallet->id;
                    $new_transaction_to_system->balance_after = $system_wallet->balance + $event->achat->amount;
                    $new_transaction_to_system->balance_before = $system_wallet->balance ;
                    $new_transaction_to_system->achatable()->associate($event->achat);
                    $new_transaction_to_system->description = "System wallet Cash in from ".$event->achat->ref_id;
                    $new_transaction_to_system->save();
                    $system_wallet->incrementBalance($event->achat->amount);

                    // Transaction to collect fee from client wallet
                    $new_transaction_get_system_fee_from_wallet = new Transaction();
                    $new_transaction_get_system_fee_from_wallet->reference = $event->achat->ref_id;
                    $new_transaction_get_system_fee_from_wallet->amount = -1*$total_fee_amount;
                    $new_transaction_get_system_fee_from_wallet->wallet_id = $wallet->id;
                    $new_transaction_get_system_fee_from_wallet->balance_after = $wallet->balance - $total_fee_amount;
                    $new_transaction_get_system_fee_from_wallet->balance_before = $wallet->balance ;
                    $new_transaction_get_system_fee_from_wallet->achatable()->associate($new_transaction);
                    $new_transaction_get_system_fee_from_wallet->description = "Fees collected for transaction ".$new_transaction->reference;
                    $new_transaction_get_system_fee_from_wallet->save();
                    $wallet->decrementBalance($total_fee_amount);

                    // Transaction to fund system fee wallet
                    $new_transaction_to_system_fee = new Transaction();
                    $new_transaction_to_system_fee->reference = $event->achat->ref_id;
                    $new_transaction_to_system_fee->amount = $total_fee_amount;
                    $new_transaction_to_system_fee->wallet_id = $system_fee_wallet->id;
                    $new_transaction_to_system_fee->balance_after = $system_fee_wallet->balance + $total_fee_amount;
                    $new_transaction_to_system_fee->balance_before = $system_fee_wallet->balance ;
                    $new_transaction_to_system_fee->achatable()->associate($new_transaction_get_system_fee_from_wallet);
                    $new_transaction_to_system_fee->description = "Fees receive for the transaction ".$new_transaction->reference;
                    $new_transaction_to_system_fee->save();
                    $system_fee_wallet->incrementBalance($total_fee_amount);


                });
            }catch (\Exception|\Throwable $e) {

                Log::error("Error has occur",["data"=>$event->achat,"error"=>$e]);


            }
        }



    }
}
