<?php

namespace App\Services;

use App\Models\ApiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;

class ApiUsageLogger
{
    public function log(string $apiKeyId, Request $request, int $responseStatus, int $responseTimeMs): void
    {
        // Extract data before queueing to avoid serialization issues
        $endpoint = $request->path();
        $method = $request->method();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $payloadHash = $this->hashPayload($request->all());
        
        Queue::push(function () use ($apiKeyId, $endpoint, $method, $ipAddress, $userAgent, $payloadHash, $responseStatus, $responseTimeMs) {
            ApiUsageLog::create([
                'api_key_id' => $apiKeyId,
                'endpoint' => $endpoint,
                'method' => $method,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'request_payload_hash' => $payloadHash,
                'response_status' => $responseStatus,
                'response_time_ms' => $responseTimeMs,
            ]);
        });
    }

    private function hashPayload(array $payload): string
    {
        return hash('sha256', json_encode($payload));
    }
}
