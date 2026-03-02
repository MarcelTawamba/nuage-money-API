<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YellowCardPayment extends Model
{
    protected $table = 'yellow_card_payments';

    protected $fillable = [
        'sequence_id',
        'yellowcard_id',
        'status',
        'amount',
        'currency',
        'customer_uid',
        'channel_id',
        'fee_amount_local',
        'fee_amount_usd',
        'service_fee_id',
        'exchange_rate',
        'amount_usd',
        'provider',
        'attempt',
        'withdrawal_id',
        'reason',
        'destination_account_name',
        'destination_account_number',
        'destination_bank_code',
        'destination_bank_name',
        'destination_network_id',
        'response_data'
    ];

    protected $casts = [
        'response_data' => 'array',
        'amount' => 'decimal:2',
        'fee_amount_local' => 'decimal:2',
        'fee_amount_usd' => 'decimal:6',
        'exchange_rate' => 'decimal:4',
        'amount_usd' => 'decimal:6',
        'attempt' => 'integer'
    ];

    /**
     * Get the achat (transaction) associated with this payment
     */
    public function achat()
    {
        return $this->morphOne(Achat::class, 'requestable');
    }
}
