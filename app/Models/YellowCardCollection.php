<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YellowCardCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_id',
        'yellowcard_id',
        'status',
        'amount',
        'currency',
        'customer_uid',
        'channel_id',
        'payment_url',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
        'amount' => 'decimal:2',
    ];
}
