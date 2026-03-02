<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to the API key pages themselves
        if ($request->is('admin/api-key*') || $request->is('admin/profile*')) {
            return $next($request);
        }

        // Check if user has an active API key with full scopes
        $user = Auth::user();
        $hasApiKey = ApiKey::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('scopes', function($query) {
                $query->where('name', '*');
            })
            ->exists();

        if (!$hasApiKey) {
            // Store the intended URL to redirect back after key generation
            session(['url.intended' => $request->fullUrl()]);
            
            // Redirect to API key page with a flash message
            return redirect()->route('users.api_key')
                ->with('warning', 'To use the dashboard and create crypto wallets, you need an API key. This only takes a moment!');
        }

        return $next($request);
    }
}
