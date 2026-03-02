<?php

namespace App\Listeners;

use App\Events\PayOutSuccessEvent;
use App\Models\ClientWallet;
use App\Models\CustomFee;
use App\Models\SystemLedger;
use App\Models\Wallet;
use App\Models\Client;
use App\Models\CountryAvaillable;
use App\Models\Operator;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Enums\PayType;
use CoreProc\WalletPlus\Models\WalletType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PayOutSuccessHandler implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(PayOutSuccessEvent $event): void
    {
        Log::info('PayOutSuccessEvent handler started for Achat ID: ' . $event->achat->id);
        
        $transaction = Transaction::where("reference", $event->achat->ref_id)->first();

        if (($event->achat->status == PaymentStatus::SUCCESSFUL) && !($transaction instanceof Transaction)) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function() use ($event) {
                    
                    $currency = WalletType::where("name", $event->achat->currency)->first();
                    
                    if (!$currency) {
                        Log::error('PayOutSuccessHandler: Currency not found', [
                            'currency' => $event->achat->currency
                        ]);
                        return;
                    }

                    $client = Client::find($event->achat->client_id);
                    $client_wallet = $client->wallet;
                    
                    if (!($client_wallet instanceof ClientWallet)) {
                        Log::error('PayOutSuccessHandler: Client wallet not found', [
                            'client_id' => $client->id
                        ]);
                        return;
                    }

                    $wallet = Wallet::where('user_type', ClientWallet::class)
                        ->where('user_id', $client_wallet->id)
                        ->where('wallet_type_id', $currency->id)
                        ->first();

                    if (!($wallet instanceof Wallet)) {
                        Log::error('PayOutSuccessHandler: Wallet not found', [
                            'client_wallet_id' => $client_wallet->id,
                            'currency_id' => $currency->id
                        ]);
                        return;
                    }

                    $system_wallet = Wallet::where('user_type', SystemLedger::class)
                        ->where('user_id', SystemLedger::whereName("system")->first()->id)
                        ->where('wallet_type_id', $currency->id)
                        ->first();
                        
                    $system_fee_wallet = Wallet::where('user_type', SystemLedger::class)
                        ->where('user_id', SystemLedger::whereName("system fee")->first()->id)
                        ->where('wallet_type_id', $currency->id)
                        ->first();

                    // Get fee configuration
                    $country = CountryAvaillable::where("code", strtolower($event->achat->country))->first();
                    $total_fee_amount = 0; // Default to 0 if no fees configured
                    
                    if ($country instanceof CountryAvaillable) {
                        $fees = Operator::where("currency_id", $currency->id)
                            ->where("country_id", $country->id)
                            ->where("type", PayType::PAY_OUT)
                            ->first();
                        
                        if ($fees instanceof Operator) {
                            $custom_fee = CustomFee::where("company_id", $client->company_id)
                                ->where("method_id", $fees->id)
                                ->first();

                            if ($custom_fee instanceof CustomFee) {
                                $fees = $custom_fee;
                            }

                            $total_fee_amount = $fees->fee_type == "percentage" 
                                ? $event->achat->amount * ($fees->fees / 100) 
                                : $fees->fees;
                        }
                    }

                    $total_fee_amount = round($total_fee_amount, 2);
                    
                    Log::info('PayOutSuccessHandler: Processing payout', [
                        'achat_id' => $event->achat->id,
                        'amount' => $event->achat->amount,
                        'fee' => $total_fee_amount,
                        'wallet_balance_before' => $wallet->balance
                    ]);

                    // Transaction to debit client wallet (payout amount)
                    $new_transaction = new Transaction();
                    $new_transaction->reference = $event->achat->ref_id;
                    $new_transaction->amount = -1 * $event->achat->amount;
                    $new_transaction->wallet_id = $wallet->id;
                    $new_transaction->balance_before = $wallet->balance;
                    $new_transaction->balance_after = $wallet->balance - $event->achat->amount;
                    $new_transaction->achatable()->associate($event->achat);
                    $new_transaction->description = "Payout via " . $event->achat->ref_id;
                    $new_transaction->save();
                    $wallet->decrementBalance($event->achat->amount);

                    // Transaction to debit system wallet
                    if ($system_wallet instanceof Wallet) {
                        $new_transaction_to_system = new Transaction();
                        $new_transaction_to_system->reference = $event->achat->ref_id;
                        $new_transaction_to_system->amount = -1 * $event->achat->amount;
                        $new_transaction_to_system->wallet_id = $system_wallet->id;
                        $new_transaction_to_system->balance_before = $system_wallet->balance;
                        $new_transaction_to_system->balance_after = $system_wallet->balance - $event->achat->amount;
                        $new_transaction_to_system->achatable()->associate($event->achat);
                        $new_transaction_to_system->description = "System wallet payout for " . $event->achat->ref_id;
                        $new_transaction_to_system->save();
                        $system_wallet->decrementBalance($event->achat->amount);
                    }

                    // Transaction to collect fee from client wallet (if applicable)
                    if ($total_fee_amount > 0) {
                        $new_transaction_get_system_fee_from_wallet = new Transaction();
                        $new_transaction_get_system_fee_from_wallet->reference = $event->achat->ref_id;
                        $new_transaction_get_system_fee_from_wallet->amount = -1 * $total_fee_amount;
                        $new_transaction_get_system_fee_from_wallet->wallet_id = $wallet->id;
                        $new_transaction_get_system_fee_from_wallet->balance_before = $wallet->balance;
                        $new_transaction_get_system_fee_from_wallet->balance_after = $wallet->balance - $total_fee_amount;
                        $new_transaction_get_system_fee_from_wallet->achatable()->associate($new_transaction);
                        $new_transaction_get_system_fee_from_wallet->description = "Payout fees for transaction " . $new_transaction->reference;
                        $new_transaction_get_system_fee_from_wallet->save();
                        $wallet->decrementBalance($total_fee_amount);

                        // Transaction to fund system fee wallet
                        if ($system_fee_wallet instanceof Wallet) {
                            $new_transaction_to_system_fee = new Transaction();
                            $new_transaction_to_system_fee->reference = $event->achat->ref_id;
                            $new_transaction_to_system_fee->amount = $total_fee_amount;
                            $new_transaction_to_system_fee->wallet_id = $system_fee_wallet->id;
                            $new_transaction_to_system_fee->balance_before = $system_fee_wallet->balance;
                            $new_transaction_to_system_fee->balance_after = $system_fee_wallet->balance + $total_fee_amount;
                            $new_transaction_to_system_fee->achatable()->associate($new_transaction_get_system_fee_from_wallet);
                            $new_transaction_to_system_fee->description = "Payout fees received for transaction " . $new_transaction->reference;
                            $new_transaction_to_system_fee->save();
                            $system_fee_wallet->incrementBalance($total_fee_amount);
                        }
                    }

                    Log::info('PayOutSuccessHandler: Payout processed successfully', [
                        'achat_id' => $event->achat->id,
                        'wallet_balance_after' => $wallet->balance
                    ]);
                });
            } catch (\Exception|\Throwable $e) {
                Log::error("PayOutSuccessHandler: Error occurred", [
                    "achat" => $event->achat,
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('PayOutSuccessHandler: Skipping - transaction already processed or status not successful', [
                'achat_id' => $event->achat->id,
                'status' => $event->achat->status,
                'transaction_exists' => $transaction instanceof Transaction
            ]);
        }
    }
}
