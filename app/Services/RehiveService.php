<?php

namespace App\Services;

use App\Models\ServiceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RehiveService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('REHIVE_API_URL', 'https://api.rehive.com/3');
    }

    private function getActiveServiceToken(): ?string
    {
        $serviceToken = ServiceToken::where('activated', true)->first();
        return $serviceToken ? $serviceToken->token : null;
    }

    public function updateTransactionStatus(array $transactions, string $status)
    {
        $token = $this->getActiveServiceToken();

        if (!$token) {
            Log::error('No active Rehive Token found');
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/admin/transaction-collections/', [
            'transactions' => $transactions,
            'status' => $status,
        ]);

        if ($response->failed()) {
            Log::error('Failed to send transaction status to Rehive.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
