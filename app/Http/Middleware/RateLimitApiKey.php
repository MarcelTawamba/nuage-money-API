<?php

namespace App\Http\Middleware;

use App\Models\RateLimitTier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('api_key');

        if (!$apiKey) {
            return $next($request);
        }

        // Get tier limits from database (cached)
        $tierName = strtolower($apiKey->rate_limit_tier ?? 'free');
        $tier = RateLimitTier::getTierByName($tierName);

        if (!$tier) {
            // Fallback to free tier if tier not found
            Log::warning("Rate limit tier not found: {$tierName}, falling back to free tier");
            $tier = RateLimitTier::getTierByName('free');
            
            if (!$tier) {
                // If still no tier, use hardcoded defaults
                $tier = [
                    'requests_per_minute' => 10,
                    'requests_per_day' => 1000,
                    'requests_per_month' => 10000,
                ];
            }
        }

        $limits = [
            'per_minute' => $tier['requests_per_minute'] ?? null,
            'per_day' => $tier['requests_per_day'] ?? null,
            'per_month' => $tier['requests_per_month'] ?? null,
        ];

        if ($limits['per_minute'] === null) {
            return $this->addRateLimitHeaders($next($request), 999999, 999999, 60);
        }

        $keyPrefix = "rate_limit:{$apiKey->id}";
        $now = now();

        $minuteKey = "{$keyPrefix}:minute:" . $now->format('Y-m-d-H-i');
        $dayKey = "{$keyPrefix}:day:" . $now->format('Y-m-d');
        $monthKey = "{$keyPrefix}:month:" . $now->format('Y-m');

        $minuteCount = (int) Cache::get($minuteKey, 0);
        $dayCount = (int) Cache::get($dayKey, 0);
        $monthCount = (int) Cache::get($monthKey, 0);

        if ($minuteCount >= $limits['per_minute']) {
            return $this->rateLimitExceeded('minute', $limits['per_minute'], 60);
        }

        if ($dayCount >= $limits['per_day']) {
            return $this->rateLimitExceeded('day', $limits['per_day'], $now->endOfDay()->diffInSeconds($now));
        }

        if ($monthCount >= $limits['per_month']) {
            return $this->rateLimitExceeded('month', $limits['per_month'], $now->endOfMonth()->diffInSeconds($now));
        }

        Cache::put($minuteKey, $minuteCount + 1, 60);
        Cache::put($dayKey, $dayCount + 1, $now->endOfDay());
        Cache::put($monthKey, $monthCount + 1, $now->endOfMonth());

        $response = $next($request);

        return $this->addRateLimitHeaders(
            $response,
            $limits['per_minute'],
            $limits['per_minute'] - $minuteCount - 1,
            60 - $now->second
        );
    }

    private function rateLimitExceeded(string $period, int $limit, int $retryAfter): Response
    {
        return response()->json([
            'error' => 'Too Many Requests',
            'message' => "Rate limit exceeded. Please try again in {$retryAfter} seconds.",
        ], 429)
            ->header('X-RateLimit-Limit', $limit)
            ->header('X-RateLimit-Remaining', 0)
            ->header('X-RateLimit-Reset', now()->addSeconds($retryAfter)->timestamp)
            ->header('Retry-After', $retryAfter);
    }

    private function addRateLimitHeaders(Response $response, int $limit, int $remaining, int $resetIn): Response
    {
        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds($resetIn)->timestamp);

        return $response;
    }
}
