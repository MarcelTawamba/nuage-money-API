<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\ApiScope;
use App\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ApiKeyController extends Controller
{
    protected ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    public function index(Request $request): JsonResponse
    {
        $apiKeys = ApiKey::where('user_id', Auth::id())
            ->with('scopes:api_scopes.id,api_scopes.name,api_scopes.description')
            ->latest()
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'environment' => $key->environment,
                    'prefix' => 'nuage_' . $key->environment . '_' . $key->key_prefix . '_****',
                    'rate_limit_tier' => $key->rate_limit_tier,
                    'is_active' => $key->is_active,
                    'scopes' => $key->scopes,
                    'last_used_at' => $key->last_used_at?->toIso8601String(),
                    'expires_at' => $key->expires_at?->toIso8601String(),
                    'created_at' => $key->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $apiKeys,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'environment' => ['required', Rule::in(['test', 'live'])],
            'rate_limit_tier' => ['nullable', Rule::in(['free', 'basic', 'premium', 'enterprise'])],
            'scope_ids' => 'nullable|array',
            'scope_ids.*' => 'exists:api_scopes,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $scopeIds = $validated['scope_ids'] ?? [];
        
        if (empty($scopeIds)) {
            $readOnlyScopes = ApiScope::whereIn('action', ['read', '*'])
                ->pluck('id')
                ->toArray();
            $scopeIds = $readOnlyScopes;
        }

        $result = $this->apiKeyService->generateKey(
            Auth::id(),
            Auth::user()->company_id ?? null,
            $validated['name'],
            $validated['environment'],
            $scopeIds,
            $validated['rate_limit_tier'] ?? 'free'
        );

        if (isset($validated['expires_at'])) {
            $result['api_key']->update(['expires_at' => $validated['expires_at']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'API key created successfully. Make sure to copy your key now as it will not be shown again.',
            'data' => [
                'id' => $result['api_key']->id,
                'name' => $result['api_key']->name,
                'key' => $result['plain_key'],
                'environment' => $result['api_key']->environment,
                'rate_limit_tier' => $result['api_key']->rate_limit_tier,
                'expires_at' => $result['api_key']->expires_at?->toIso8601String(),
                'scopes' => $result['api_key']->scopes()->get(['api_scopes.id', 'api_scopes.name', 'api_scopes.description']),
            ],
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $apiKey = ApiKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['scopes:api_scopes.id,api_scopes.name,api_scopes.description', 'usageLogs' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'environment' => $apiKey->environment,
                'prefix' => 'nuage_' . $apiKey->environment . '_' . $apiKey->key_prefix . '_****',
                'rate_limit_tier' => $apiKey->rate_limit_tier,
                'is_active' => $apiKey->is_active,
                'scopes' => $apiKey->scopes,
                'last_used_at' => $apiKey->last_used_at?->toIso8601String(),
                'expires_at' => $apiKey->expires_at?->toIso8601String(),
                'created_at' => $apiKey->created_at->toIso8601String(),
                'recent_usage' => $apiKey->usageLogs->map(function ($log) {
                    return [
                        'endpoint' => $log->endpoint,
                        'method' => $log->method,
                        'status' => $log->response_status,
                        'response_time_ms' => $log->response_time_ms,
                        'created_at' => $log->created_at->toIso8601String(),
                    ];
                }),
            ],
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $apiKey = ApiKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'scope_ids' => 'sometimes|array',
            'scope_ids.*' => 'exists:api_scopes,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $apiKey->name = $validated['name'];
        }

        if (isset($validated['is_active'])) {
            $apiKey->is_active = $validated['is_active'];
        }

        $apiKey->save();

        if (isset($validated['scope_ids'])) {
            $apiKey->scopes()->sync($validated['scope_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'API key updated successfully',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'is_active' => $apiKey->is_active,
                'scopes' => $apiKey->scopes()->get(['api_scopes.id', 'api_scopes.name', 'api_scopes.description']),
            ],
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $apiKey = ApiKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $apiKey->delete();

        return response()->json([
            'success' => true,
            'message' => 'API key revoked successfully',
        ]);
    }

    public function regenerate(string $id): JsonResponse
    {
        $apiKey = ApiKey::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result = $this->apiKeyService->rotateKey($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate API key',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'API key regenerated successfully. Make sure to copy your new key now as it will not be shown again.',
            'data' => [
                'id' => $result['api_key']->id,
                'name' => $result['api_key']->name,
                'key' => $result['plain_key'],
                'environment' => $result['api_key']->environment,
            ],
        ]);
    }
}
