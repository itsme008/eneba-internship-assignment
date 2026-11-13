<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApiInputs
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->is('api/v1/*')) {
            return $next($request);
        }
        $path = $request->path();
        $method = $request->method();
        $allowed = match (true) {
            $path === 'api/v1/auth/register' => ['email', 'display_name', 'password', 'password_confirmation', 'claim_order_id'],
            $path === 'api/v1/auth/login' => ['email', 'password', 'claim_order_id'],
            $path === 'api/v1/me/password' => ['current_password', 'password', 'password_confirmation'],
            $path === 'api/v1/me' && $method === 'PATCH' => ['display_name'],
            $path === 'api/v1/products' || $path === 'api/v1/search/suggestions' => ['currency', 'q', 'page', 'sort', 'type', 'platform', 'region', 'genre', 'price_min_minor', 'price_max_minor'],
            $path === 'api/v1/products/by-ids' => ['currency', 'ids'],
            str_starts_with($path, 'api/v1/products/') => ['currency'],
            $path === 'api/v1/cart/preview' => ['currency', 'items'],
            $path === 'api/v1/cart/merge' => ['currency', 'transfer_id', 'items'],
            str_starts_with($path, 'api/v1/cart/items/') => ['currency', 'quantity', 'expected_revision'],
            $path === 'api/v1/cart' => ['currency'],
            $path === 'api/v1/checkout/quote' => ['currency', 'expected_revision', 'email', 'items'],
            $path === 'api/v1/checkout/attempts' => ['quote_token', 'payment_method', 'simulate_failure'],
            str_starts_with($path, 'api/v1/homepage/sections/') => ['currency', 'ids'],
            $path === 'api/v1/orders' => ['page'],
            $path === 'api/v1/library' => ['page', 'q'],
            default => [],
        };
        $unexpected = array_diff(array_keys($request->all()), $allowed);
        if ($unexpected) {
            throw ValidationException::withMessages(array_fill_keys($unexpected, ['Unsupported field.']));
        }
        if (is_array($request->input('items'))) {
            foreach ($request->input('items', []) as $i => $item) {
                if (is_array($item) && array_diff(array_keys($item), ['offer_id', 'quantity'])) {
                    throw ValidationException::withMessages(["items.$i" => 'Only offer_id and quantity are accepted.']);
                }
            }
        }

        return $next($request);
    }
}
