<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API routes, don't redirect - let the exception handler return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        return route('login');
    }

    /**
     * Handle unauthenticated requests
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API routes, throw an authentication exception that will be caught
        // and converted to JSON by the exception handler
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json([
                'message' => 'Unauthenticated. Please login to access this resource.',
                'error' => 'unauthenticated'
            ], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}
