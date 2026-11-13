<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiContract
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }
        $requestId = (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);
        if (strlen($request->getContent()) > 262144) {
            abort(413, 'Request body is too large.');
        }
        if ($request->isJson() && trim($request->getContent()) !== '') {
            try {
                $json = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
                abort_unless(is_array($json), 400, 'Expected a JSON object.');
            } catch (\JsonException) {
                abort(400, 'Malformed JSON request.');
            }
        }
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
