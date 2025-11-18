<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'status',
        'fee_amount',
        'merchant_id',
        'transaction_reference',
        'customer_email',
        'payment_partner_id',
        'dva_account_number',
        'amount',
        'initial_amount',
        'currency',
        'narration',
        'authorization_code',
        'payer_information',
    ];

    protected $casts = [
        'payer_information' => 'array',
        'fee_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'initial_amount' => 'decimal:2',
    ];
}
