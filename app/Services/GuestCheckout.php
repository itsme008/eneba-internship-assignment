<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuestCheckout
{
    public function __construct(public Cart $carts) {}

    public function identity(Request $request, ?string $email = null): string
    {
        $id = $request->session()->get('guest_checkout_id');
        if ($id && DB::table('guest_checkouts')->where('id', $id)->exists()) {
            return $id;
        }
        abort_unless($email, 404, 'Order not found.');
        $id = (string) Str::uuid();
        DB::table('guest_checkouts')->insert(['id' => $id, 'email' => $email, 'created_at' => now(), 'updated_at' => now()]);
        $request->session()->put('guest_checkout_id', $id);

        return $id;
    }

    public function snapshot(string $guest, string $email, array $items, string $currency): array
    {
        return DB::transaction(function () use ($guest, $email, $items, $currency) {
            $cart = $this->carts->lockedGuest($guest);
            usort($items, fn ($a, $b) => strcmp($a['offer_id'], $b['offer_id']));
            $old = DB::table('cart_items')->where('cart_id', $cart->id)->orderBy('offer_id')->get(['offer_id', 'quantity'])->map(fn ($i) => (array) $i)->all();
            $changed = $old !== $items || DB::table('guest_checkouts')->where('id', $guest)->value('email') !== $email;
            if ($changed) {
                $view = $this->carts->render($cart, $items, $currency);
                abort_unless($view['can_checkout'], 409, 'Review your cart: an item is unavailable or the cart is empty.');
                DB::table('cart_items')->where('cart_id', $cart->id)->delete();
                foreach ($items as $item) {
                    DB::table('cart_items')->insert(['id' => (string) Str::uuid(), 'cart_id' => $cart->id, ...$item]);
                }
                DB::table('guest_checkouts')->where('id', $guest)->update(['email' => $email, 'updated_at' => now()]);
                $this->carts->touch($cart);
            }

            return $this->carts->readGuest($guest, $currency);
        }, 3);
    }

    public function claim(Request $request, string $orderId, string $userId, string $email): void
    {
        $guest = $request->session()->get('guest_checkout_id');
        abort_unless($guest, 404, 'Order not found.');
        DB::transaction(function () use ($guest, $orderId, $userId, $email) {
            DB::table('guest_checkouts')->where('id', $guest)->lockForUpdate()->first();
            $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();
            if ($order && $order->user_id === $userId) {
                return;
            }
            abort_unless($order && $order->guest_checkout_id === $guest && ! $order->user_id, 404, 'Order not found.');
            abort_unless(hash_equals($order->buyer_email_snapshot, $email), 422, 'Use the email address entered for this purchase.');
            DB::table('orders')->where('id', $orderId)->update(['user_id' => $userId, 'guest_checkout_id' => null]);
        }, 3);
    }

    public function authorizedOrder(Request $request, string $id): object
    {
        $order = DB::table('orders')->where('id', $id)->first();
        $owned = $order && (($request->user() && $order->user_id === $request->user()->id) || (! $order->user_id && $order->guest_checkout_id && $order->guest_checkout_id === $request->session()->get('guest_checkout_id')));
        abort_unless($owned,404,'Order not found.');

        return $order;
    }
}
