<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'status',
        'from_amount',
        'to_amount',
        'from_currency',
        'to_currency',
        'merchant_id',
        'transaction_reference',
        'amount',
        'currency',
        'fee_amount',
        'authorization_code',
    ];

    protected $casts = [
        'from_amount' => 'decimal:2',
        'to_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
    ];
}
