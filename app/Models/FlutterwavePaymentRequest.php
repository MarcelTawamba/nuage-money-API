<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlutterwavePaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'type',
        'status',
        'amount',
        'currency',
        'flw_customer_id',
        'flw_payment_method_id',
        'flw_charge_id',
        'flw_transfer_id',
        'flw_refund_id',
        'payment_channel',
        'recipient_name',
        'recipient_phone',
        'recipient_bank_code',
        'recipient_account_number',
        'fee_amount',
        'fee_currency',
        'exchange_rate',
        'attempt',
        'metadata',
    ];

    protected $casts = [
        'metadata'      => 'array',
        'amount'        => 'float',
        'fee_amount'    => 'float',
        'exchange_rate' => 'float',
    ];

    /**
     * The Achat (transaction) that owns this payment request.
     * Matches the polymorphic requestable pattern used across the codebase.
     */
    public function achat()
    {
        return $this->morphOne(\App\Models\Achat::class, 'requestable');
    }
}
