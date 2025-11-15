<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class LogViewerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if token is in query parameter or bearer token
        $token = $request->query('token') ?? $request->bearerToken();

        if ($token) {
            $personalAccessToken = PersonalAccessToken::findToken($token);

            if ($personalAccessToken && $personalAccessToken->tokenable->hasRole('admin')) {
                // Set the authenticated user for this request
                auth()->setUser($personalAccessToken->tokenable);

                // Try to store in session if available
                if ($request->hasSession()) {
                    $request->session()->put('log_viewer_user_id', $personalAccessToken->tokenable->id);
                    $request->session()->save();
                }

                return $next($request);
            }
        }

        // Check if user is already authenticated in session (if session exists)
        if ($request->hasSession()) {
            $userId = $request->session()->get('log_viewer_user_id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user && $user->hasRole('admin')) {
                    auth()->setUser($user);
                    return $next($request);
                }
            }
        }

        // Check regular authentication
        $user = $request->user('sanctum') ?? $request->user('web') ?? $request->user();
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        // Unauthorized
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        abort(403, 'Unauthorized. Please use the correct access URL.');
    }
}
