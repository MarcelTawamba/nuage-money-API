<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'status',
        'merchant_id',
        'transaction_reference',
        'customer_email',
        'user_transaction_reference',
        'payment_code',
        'gateway_reference',
        'fee_amount',
        'narration',
        'amount',
        'currency',
        'authorization_code',
        'extra_information',
        'payer_information',
    ];

    protected $casts = [
        'extra_information' => 'array',
        'payer_information' => 'array',
        'fee_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];
}
