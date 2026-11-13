<?php

use App\Http\Controllers\IdentityController;
use App\Http\Controllers\MarketplaceController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function () {
    Route::get('auth/csrf', [IdentityController::class, 'csrf']);
    Route::post('auth/register', [IdentityController::class, 'register'])->middleware('throttle:registration');
    Route::post('auth/login', [IdentityController::class, 'login'])->middleware('throttle:login');
    foreach (['bootstrap' => 'bootstrap', 'products' => 'products', 'products/by-ids' => 'byIds', 'search/suggestions' => 'suggestions', 'homepage/sections' => 'sections'] as $path => $action) {
        Route::get($path, [MarketplaceController::class, $action]);
    }
    Route::get('products/{slug}/offers', [MarketplaceController::class, 'offers']);
    Route::get('products/{slug}', [MarketplaceController::class, 'detail']);
    Route::get('homepage/sections/{key}', [MarketplaceController::class, 'section']);
    Route::post('cart/preview', [MarketplaceController::class, 'preview']);
    Route::get('health/live', fn () => response()->json(['data' => ['status' => 'ok']]));
    Route::get('health/ready', function () {
        DB::table('products')->limit(1)->exists();

        return response()->json(['data' => ['status' => 'ready']]);
    });
    Route::post('checkout/quote', [MarketplaceController::class, 'quote'])->middleware(['auth', 'throttle:checkout-quote']);
    Route::post('checkout/attempts', [MarketplaceController::class, 'purchase'])->middleware(['auth', 'throttle:checkout-attempt']);
    Route::get('checkout/orders/{id}', [MarketplaceController::class, 'order'])->whereUuid('id');
    Route::post('checkout/orders/{id}/email', [MarketplaceController::class, 'email'])->whereUuid('id')->middleware('throttle:order-email');
    Route::middleware('auth')->group(function () {
        Route::post('auth/logout', [IdentityController::class, 'logout']);
        Route::get('me', [IdentityController::class, 'me']);
        Route::patch('me', [IdentityController::class, 'update']);
        Route::post('me/password', [IdentityController::class, 'password'])->middleware('throttle:password');
        Route::get('cart', [MarketplaceController::class, 'cart']);
        Route::put('cart/items/{offer}', [MarketplaceController::class, 'setItem'])->whereUuid('offer');
        Route::delete('cart/items/{offer}', [MarketplaceController::class, 'deleteItem'])->whereUuid('offer');
        Route::post('cart/merge', [MarketplaceController::class, 'merge']);
        Route::post('checkout/orders/{id}/claim', [MarketplaceController::class, 'claim'])->whereUuid('id');
        Route::get('library', [MarketplaceController::class, 'library']);
        Route::get('orders', [MarketplaceController::class, 'orders']);
        Route::get('orders/{id}', [MarketplaceController::class, 'order'])->whereUuid('id');
    });
    Route::any('{path}', fn () => abort(404))->where('path', '.*');
});
Route::get('/{path?}', fn () => view('app'))->where('path', '.*');
