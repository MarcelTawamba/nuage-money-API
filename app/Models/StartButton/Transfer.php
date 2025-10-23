<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'status',
        'fee_amount',
        'merchant_id',
        'transaction_reference',
        'gateway_reference',
        'amount',
        'currency',
        'recipient',
        'authorization_code',
    ];

    protected $casts = [
        'recipient' => 'array',
        'fee_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];
}
