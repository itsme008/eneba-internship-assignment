<?php

use App\Http\Middleware\ApiContract;
use App\Http\Middleware\ApiInputs;
use App\Http\Middleware\SessionVersion;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ApiContract::class);
        $middleware->web(append: [SessionVersion::class, ApiInputs::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            $status = $e instanceof ValidationException ? 422 : ($e instanceof AuthenticationException ? 401 : ($e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500));
            $codes = [400 => 'BAD_REQUEST', 401 => 'UNAUTHENTICATED', 403 => 'FORBIDDEN', 404 => 'NOT_FOUND', 409 => 'CONFLICT', 413 => 'PAYLOAD_TOO_LARGE', 419 => 'CSRF_TOKEN_MISMATCH', 422 => 'VALIDATION_FAILED', 429 => 'RATE_LIMITED'];
            $message = $status >= 500 ? 'Something went wrong. Please try again.' : ($status === 401 ? 'Please sign in to continue.' : ($status === 422 && $e instanceof ValidationException ? 'Please check the highlighted details.' : $e->getMessage()));

            return response()->json(['error' => ['code' => $codes[$status] ?? 'INTERNAL_ERROR', 'message' => $message, 'fields' => $e instanceof ValidationException ? $e->errors() : (object) [], 'details' => (object) []]], $status)->header('Cache-Control', 'private, no-store')->header('X-Request-ID', $request->attributes->get('request_id', ''));
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
