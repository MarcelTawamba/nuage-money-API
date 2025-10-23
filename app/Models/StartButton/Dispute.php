<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_reference',
        'dispute_reference',
        'currency',
        'amount',
        'elapse_time',
        'type',
        'customer_email',
        'status',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
