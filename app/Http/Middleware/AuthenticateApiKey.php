<?php

namespace App\Http\Middleware;

use App\Services\ApiKeyService;
use App\Services\ApiUsageLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    protected ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API key is required. Please provide your API key in the Authorization header as "Bearer nuage_..."',
            ], 401);
        }

        $apiKey = substr($authHeader, 7);
        
        $validatedKey = $this->apiKeyService->validateKey($apiKey);

        if (!$validatedKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired API key',
            ], 401);
        }

        if ($scope && !$validatedKey->hasScope($scope)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "This API key does not have the required scope: {$scope}",
            ], 403);
        }

        $request->attributes->set('api_key', $validatedKey);
        
        $validatedKey->updateLastUsed();

        $startTime = microtime(true);
        $response = $next($request);
        $responseTime = (int) ((microtime(true) - $startTime) * 1000);

        app(ApiUsageLogger::class)->log(
            $validatedKey->id,
            $request,
            $response->status(),
            $responseTime
        );

        return $response;
    }
}
