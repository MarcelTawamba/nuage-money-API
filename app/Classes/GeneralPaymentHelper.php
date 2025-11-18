<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PayType;
use App\Models\Achat;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\CountryAvaillable;
use App\Models\CustomFee;
use App\Models\ExchangeRequest;
use App\Models\Operator;
use App\Models\SystemLedger;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

abstract class GeneralPaymentHelper
{
    abstract public static function initPayment(array $input);

    abstract public static function checkRequestPayments(Achat $achat);

    abstract public static function checkRequestPayout(Achat $achat);

    abstract public static function initPayout(array $input);

    abstract public static function generateMomentTime();

    /**
     * Create or retrieve user, company, client, and wallet for payout
     * This ensures consistency across all payment providers
     * 
     * @param array $input
     * @return array ['user' => User, 'client' => Client, 'wallet' => Wallet, 'client_wallet' => ClientWallet]
     */
    public static function setupUserAndWallet(array $input): array
    {
        $user = User::firstOrCreate(
            ['email' => $input['user_email']],
            [
                'name' => $input['first_name'].' '.$input['last_name'],
                'password' => bcrypt(Str::random(10)),
                'country_code' => $input['country'],
                'phone_number' => $input['user_phone_number'] ?? null,
            ]
        );

        $company = \App\Models\Company::firstOrCreate(
            ['name' => $input['company_name'] ?? 'Default Company'],
            [
                'user_id' => $user->id,
                'company_type' => 'fintech',
                'address' => '55 University Avenue, Suite 1100, Toronto, Ontario M5J 2H7',
                'phone_number' => $input['company_phone_number'] ?? $input['user_phone_number'] ?? null,
            ]
        );

        $client = Client::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'name' => $input['first_name'].' '.$input['last_name'],
                'company_id' => $company->id,
                'secret' => Str::random(40),
                'redirect' => '/',
                'personal_access_client' => false,
                'password_client' => false,
                'revoked' => false,
                'is_live' => true,
                'main_wallet' => $input['currency'],
            ]
        );

        $clientWallet = ClientWallet::firstOrCreate(['client_id' => $client->id]);

        $walletType = WalletType::firstOrCreate(
            ['name' => $input['currency']], 
            ['decimals' => 0]
        );

        $wallet = Wallet::firstOrNew(
            [
                'user_type' => ClientWallet::class,
                'user_id' => $clientWallet->id,
                'wallet_type_id' => $walletType->id,
            ]
        );

        if (!$wallet->exists) {
            $wallet->raw_balance = $input['account_balance'] ?? 0;
            $wallet->save();
        }

        return [
            'user' => $user,
            'client' => $client,
            'wallet' => $wallet,
            'client_wallet' => $clientWallet,
        ];
    }

    /**
     * Create Achat record for payout with proper associations
     * 
     * @param int $clientId
     * @param array $input
     * @param string $refIdPrefix - e.g., 'BRIDGE-', 'VALR-'
     * @return Achat
     */
    public static function createAchatForPayout(int $clientId, array $input, string $refIdPrefix): Achat
    {
        $new_achat = new Achat;
        $new_achat->client_id = $clientId;
        $new_achat->amount = -1 * $input['amount'];
        $new_achat->country = $input['country'];
        $new_achat->currency = $input['currency'];
        $new_achat->user_ref_id = $input['ref_id'];
        $new_achat->ref_id = $refIdPrefix . static::UUID();

        return $new_achat;
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public static function checkPayments($input): \Illuminate\Http\JsonResponse
    {

        $request = Achat::where('user_ref_id', $input['ref_id'])->where('client_id', $input['service'])->where('amount', '>', 0)->first();

        if (! ($request instanceof Achat)) {
            return response()->json([
                'message' => __('does_not_exist', ['attribute' => 'ref_id']),
                'errors' => [
                    'ref_id' => __('does_not_exist', ['attribute' => 'ref_id']),
                ],
            ]);
        }

        if ($request->status == PaymentStatus::PENDING || $request->status == PaymentStatus::CREATED) {
            return response()->json(static::checkRequestPayments($request));
        }

        return response()->json([
            'pay_token' => $request->ref_id,
            'amount' => $request->amount,
            'status' => $request->status,
            'ref_id' => $request->user_ref_id,
            'payment_method' => $input['payment_method'],
        ]);

    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public static function checkPayout($input): \Illuminate\Http\JsonResponse
    {

        $request = Achat::where('user_ref_id', $input['ref_id'])->where('client_id', $input['service'])->where('amount', '<', 0)->first();

        if (! ($request instanceof Achat)) {
            return response()->json([
                'message' => __('does_not_exist', ['attribute' => 'ref_id']),
                'errors' => [
                    __('common.does_not_exist', ['attribute' => 'ref_id']),
                ],
            ]);
        }

        if ($request->status == PaymentStatus::PENDING || $request->status == PaymentStatus::CREATED) {

            return response()->json(static::checkRequestPayout($request));
        }

        return response()->json([
            'pay_token' => $request->ref_id,
            'amount' => $request->amount,
            'status' => $request->status,
            'ref_id' => $request->user_ref_id,
            'payment_method' => PaymentMethod::TOUPESU_MOBILE,
        ]);

    }

    public static function UUID(): string
    {

        return Str::uuid();

    }

    public static function saveTransaction(Achat $achat): bool
    {

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($achat) {

                $currency = WalletType::where('name', $achat->currency)->first();

                $client = Client::find($achat->client_id);
                $client_wallet = $client->wallet;
                if (! ($client_wallet instanceof ClientWallet)) {
                    $client_wallet = new ClientWallet;
                    $client_wallet->client_id = $client->id;
                    $client_wallet->save();
                }

                $wallet = Wallet::where('user_type', ClientWallet::class)->where('user_id', $client->wallet->id)->where('wallet_type_id', $currency->id)->first();

                if (Str::startsWith($achat->ref_id, 'StartButton-')) {
                    $adminUser = User::where('service_provider', 'StartButton')->first();
                } else {
                    // As per user request, throw an error for other service providers for now.
                    throw new \Exception('Admin user seeder for this service provider is not yet created.');
                }

                if (! $adminUser) {
                    // This will be caught if the StartButton admin user is not found.
                    throw new \Exception('Admin user for the service provider not found. Please run the seeder.');
                }

                $system_wallet = Wallet::firstOrCreate(
                    [
                        'user_type' => SystemLedger::class,
                        'user_id' => $adminUser->id,
                        'wallet_type_id' => $currency->id,
                    ],
                    ['raw_balance' => 0]
                );
                $system_fee_wallet = Wallet::firstOrCreate(
                    [
                        'user_type' => SystemLedger::class,
                        'user_id' => $adminUser->id,
                        'wallet_type_id' => $currency->id,
                    ],
                    ['raw_balance' => 0]
                );

                $country = CountryAvaillable::where('code', strtolower($achat->country))->first();

                if ($achat->requestable_type == ExchangeRequest::class) {
                    $total_fee_amount = 0;

                } else {
                    $currency_id = $currency->id;
                    $country_id = $country->id ?? 30;
                    $fees = Operator::where('currency_id', $currency_id)->where('country_id', $country_id)->where('type', PayType::PAY_OUT)->first();
                    if ($fees) {
                        $custom_fee = CustomFee::where('company_id', $client->company_id)->where('method_id', $fees->id)->first();

                        if ($custom_fee instanceof CustomFee) {
                            $fees = $custom_fee;
                        }
                        $total_fee_amount = $fees->fee_type == 'percentage' ? -1 * $achat->amount * ($fees->fees / 100) : $fees->fees;
                    } else {
                        $total_fee_amount = 0;
                    }
                }

                $total_fee_amount = round($total_fee_amount, 2);

                // Transaction to remove fund from client wallet
                $new_transaction = new Transaction;
                $new_transaction->reference = $achat->ref_id;
                $new_transaction->amount = $achat->amount;
                $new_transaction->wallet_id = $wallet->id;
                $new_transaction->balance_before = $wallet->raw_balance;
                $new_transaction->balance_after = $wallet->raw_balance + $achat->amount;
                $new_transaction->description = 'Cash out from '.$achat->ref_id;
                $new_transaction->achatable()->associate($achat);
                $new_transaction->save();

                $wallet->decrementBalance(-1 * $achat->amount);

                // Transaction to remove fund from system wallet
                $new_transaction_to_system = new Transaction;
                $new_transaction_to_system->reference = $achat->ref_id;
                $new_transaction_to_system->amount = $achat->amount;
                $new_transaction_to_system->wallet_id = $system_wallet->id;
                $new_transaction_to_system->balance_after = $system_wallet->raw_balance + $achat->amount;
                $new_transaction_to_system->balance_before = $system_wallet->raw_balance;
                $new_transaction_to_system->achatable()->associate($achat);
                $new_transaction_to_system->description = 'System wallet Cash out from '.$achat->ref_id;
                $new_transaction_to_system->save();
                $system_wallet->decrementBalance(-1 * $achat->amount);

                // Transaction to collect fee from client wallet
                $new_transaction_get_system_fee_from_wallet = new Transaction;
                $new_transaction_get_system_fee_from_wallet->reference = $achat->ref_id;
                $new_transaction_get_system_fee_from_wallet->amount = -1 * $total_fee_amount;
                $new_transaction_get_system_fee_from_wallet->wallet_id = $wallet->id;
                $new_transaction_get_system_fee_from_wallet->balance_after = $wallet->raw_balance - $total_fee_amount;
                $new_transaction_get_system_fee_from_wallet->balance_before = $wallet->raw_balance;
                $new_transaction_get_system_fee_from_wallet->achatable()->associate($new_transaction);
                $new_transaction_get_system_fee_from_wallet->description = 'Fees collected for transaction '.$new_transaction->reference;
                $new_transaction_get_system_fee_from_wallet->save();
                $wallet->decrementBalance($total_fee_amount);

                // Transaction to fund system fee wallet
                $new_transaction_to_system_fee = new Transaction;
                $new_transaction_to_system_fee->reference = $achat->ref_id;
                $new_transaction_to_system_fee->amount = $total_fee_amount;
                $new_transaction_to_system_fee->wallet_id = $system_fee_wallet->id;
                $new_transaction_to_system_fee->balance_after = $system_fee_wallet->raw_balance + $total_fee_amount;
                $new_transaction_to_system_fee->balance_before = $system_fee_wallet->raw_balance;
                $new_transaction_to_system_fee->achatable()->associate($new_transaction_get_system_fee_from_wallet);
                $new_transaction_to_system_fee->description = 'Fees receive for the transaction '.$new_transaction->reference;
                $new_transaction_to_system_fee->save();
                $system_fee_wallet->incrementBalance($total_fee_amount);
            });

            return true;
        } catch (Exception|\Throwable $e) {
            info('Error has occur', ['data' => $achat, 'error' => $e]);

            return false;
        }
    }
}
