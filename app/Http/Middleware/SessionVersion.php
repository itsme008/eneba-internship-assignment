<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && (int) $request->session()->get('auth_version', 0) !== (int) $request->user()->auth_version) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Please sign in again.']], 401);
        }
        $response = $next($request);
        if ($request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-store');
        }

        return $response;
    }
}
