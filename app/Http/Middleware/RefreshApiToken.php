<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RefreshApiToken
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $token = $user->currentAccessToken();

            // Check if token is about to expire (within 1 hour)
            // Only check if token exists and is not a TransientToken
            if ($token && !($token instanceof \Laravel\Sanctum\TransientToken) && $token->expires_at && $token->expires_at->subHour()->isPast()) {
                try {
                    // Delete the current token
                    $token->delete();

                    // Create a new token
                    $newToken = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;

                    // Store the new token in session
                    session(['api_token' => $newToken]);

                    Log::info('API token refreshed', [
                        'user_id' => $user->id,
                        'new_token_expires_at' => now()->addHours(24)
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to refresh API token', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $next($request);
    }
} 