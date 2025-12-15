<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiUsageLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'api_key_id',
        'endpoint',
        'method',
        'ip_address',
        'user_agent',
        'request_payload_hash',
        'response_status',
        'response_time_ms',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
