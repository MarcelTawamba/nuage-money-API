<?php

namespace App\Services\StartButton;

use App\Models\Achat;
use App\Models\StartButton\Topup;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use Illuminate\Support\Facades\Log;

class TopupService
{
    public function handle(object $data)
    {
        // Create a new topup record
        $topup = Topup::create([
            'transaction_id' => $data->transaction->_id,
            'transaction_type' => $data->transaction->transType,
            'status' => $data->transaction->status,
            'fee_amount' => ($data->transaction->feeAmount ?? 0) / 100,
            'merchant_id' => $data->transaction->merchantId,
            'transaction_reference' => $data->transaction->transactionReference,
            'customer_email' => $data->transaction->customerEmail,
            'payment_partner_id' => $data->transaction->paymentPartnerId ?? null,
            'dva_account_number' => $data->transaction->dvaAccountNumber ?? null,
            'amount' => $data->transaction->amount / 100,
            'initial_amount' => ($data->transaction->initialAmount ?? 0) / 100,
            'currency' => $data->transaction->currency,
            'narration' => $data->transaction->narration ?? null,
            'authorization_code' => $data->authorizationCode ?? null,
            'payer_information' => isset($data->payerInformation) ? json_encode($data->payerInformation) : null,
        ]);

        Log::channel('slack')->info('StartButton Topup record created', ['topup' => $topup]);

        // TODO: Implement the logic to credit the user's wallet.
        // This will likely involve:
        // 1. Finding the user/wallet associated with the dva_account_number.
        // 2. Crediting the wallet with the topup amount.
        // 3. Dispatching any necessary events.

    }
}
