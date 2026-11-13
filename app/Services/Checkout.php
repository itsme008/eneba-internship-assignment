<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Checkout
{
    public function __construct(public Cart $carts, public Money $money) {}

    private function lines(array $cart): array
    {
        $base = DB::table('offers')->whereIn('id', array_column($cart['items'], 'offer_id'))->pluck('price_eur_minor', 'id');

        return array_map(fn ($i) => ['offer_id' => $i['offer_id'], 'quantity' => $i['quantity'], 'base_unit_eur_minor' => (string) $base[$i['offer_id']], 'unit_minor' => $i['unit_price']['minor']], $cart['items']);
    }

    public function quote(?string $user, string $currency, string $revision, ?string $guest = null): array
    {
        abort_unless($user && ! $guest, 401, 'Sign in before payment.');
        $cart = $guest ? $this->carts->readGuest($guest, $currency) : $this->carts->read($user, $currency);
        abort_unless($cart['revision'] === $revision, 409, 'Your cart changed.');
        abort_unless($cart['can_checkout'], 409, 'Review your cart: an item is unavailable or the cart is empty.');
        $email = $guest ? DB::table('guest_checkouts')->where('id', $guest)->value('email') : DB::table('users')->where('id', $user)->value('email');
        $payload = ['v' => 2, 'user' => $user, 'guest' => $guest, 'email' => $email, 'cart' => $cart['id'], 'revision' => $revision, 'currency' => $currency, 'fx' => $cart['fx_version'],
            'lines' => $this->lines($cart), 'total' => $cart['total']['minor'], 'expires' => now()->addMinutes(10)->timestamp];

        return [...$cart, 'cart_id' => $cart['id'], 'cart_revision' => $revision, 'quote_token' => Crypt::encryptString(json_encode($payload)), 'expires_at' => date(DATE_ATOM, $payload['expires'])];
    }

    public function purchase(?string $user, string $key, string $token, string $method, bool $fail, int $authVersion, ?string $guest = null): array
    {
        abort_unless($user && ! $guest, 401, 'Sign in before payment.');
        $hash = hash('sha256', json_encode([$token, $method, $fail]));

        return DB::transaction(function () use ($user, $key, $token, $method, $fail, $hash, $authVersion, $guest) {
            // All user/cart mutations take the same user-first lock order, including attempt insertion.
            $cart = $guest ? $this->carts->lockedGuest($guest) : $this->carts->locked($user);
            abort_unless($guest || (int) DB::table('users')->where('id', $user)->value('auth_version') === $authVersion, 401, 'Please sign in again.');
            $old = DB::table('checkout_attempts')->where('user_id', $user)->where('guest_checkout_id', $guest)->where('idempotency_key', $key)->lockForUpdate()->first();
            if ($old) {
                abort_unless(hash_equals($old->request_hash, $hash), 409, 'Purchase identity was reused with different contents.');

                return ['http' => $old->result_http_status, 'body' => json_decode($old->result_payload, true), 'replayed' => true];
            }
            try {
                $quote = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                abort(422, 'Invalid checkout quote.');
            }
            abort_unless(($quote['v'] ?? null) === 2 && ($quote['user'] ?? null) === $user && ($quote['guest'] ?? null) === $guest && ($quote['cart'] ?? null) === $cart->id, 422, 'Invalid checkout quote.');
            $attempt = (string) Str::uuid();
            DB::table('checkout_attempts')->insert(['id' => $attempt, 'user_id' => $user, 'guest_checkout_id' => $guest, 'cart_id' => $cart->id, 'idempotency_key' => $key, 'request_hash' => $hash, 'status' => 'processing', 'payment_method' => $method, 'simulate_failure' => $fail, 'created_at' => now(), 'updated_at' => now()]);
            $finish = function (string $status, int $http, array $body) use ($attempt) {
                DB::table('checkout_attempts')->where('id', $attempt)->update(['status' => $status, 'result_http_status' => $http, 'result_payload' => json_encode($body), 'completed_at' => now(), 'updated_at' => now()]);

                return ['http' => $http, 'body' => $body, 'replayed' => false];
            };
            $reject = fn (string $code, string $message) => $finish('rejected', 409, ['error' => ['code' => $code, 'message' => $message, 'fields' => (object) [], 'details' => (object) []]]);
            if ($quote['expires'] < now()->timestamp) {
                return $reject('QUOTE_EXPIRED', 'Your quote expired. Review the cart and confirm again.');
            }
            if ((string) $cart->revision !== $quote['revision']) {
                return $reject('CART_VERSION_CONFLICT', 'Your cart changed. Review it before paying.');
            }
            $offerIds = array_column($quote['lines'], 'offer_id');
            sort($offerIds);
            $offers = DB::table('offers')->whereIn('id', $offerIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $view = $guest ? $this->carts->readGuest($guest, $quote['currency']) : $this->carts->read($user, $quote['currency']);
            if (! $view['can_checkout']) {
                return $reject('INSUFFICIENT_STOCK', 'Some items are unavailable. Your whole cart is unchanged.');
            }
            if (($guest && DB::table('guest_checkouts')->where('id', $guest)->value('email') !== $quote['email']) || $view['fx_version'] !== $quote['fx'] || $this->lines($view) !== $quote['lines'] || $view['total']['minor'] !== $quote['total']) {
                return $reject('QUOTE_CHANGED', 'Prices changed. Review a new quote before paying.');
            }
            if ($fail) {
                return $finish('declined', 200, ['data' => ['attempt_id' => $attempt, 'status' => 'declined', 'code' => 'SIMULATED_PAYMENT_FAILED', 'message' => 'Demo payment declined. Your cart and stock are unchanged.']]);
            }
            $order = (string) Str::uuid();
            $number = 'EN-'.strtoupper(Str::random(10));
            $rate = $this->money->rate($quote['currency']);
            $baseTotal = 0;
            foreach ($view['items'] as $i) {
                $baseTotal += (int) $offers[$i['offer_id']]->price_eur_minor * $i['quantity'];
            }
            DB::table('orders')->insert(['id' => $order, 'order_number' => $number, 'checkout_attempt_id' => $attempt, 'user_id' => $user, 'guest_checkout_id' => $guest, 'buyer_email_snapshot' => $quote['email'], 'status' => 'completed', 'payment_method' => $method, 'currency' => $quote['currency'], 'rate_set_id' => $rate->rate_set_id, 'exchange_rate_snapshot' => $rate->rate, 'subtotal_minor' => $quote['total'], 'total_minor' => $quote['total'], 'base_total_eur_minor' => $baseTotal, 'purchased_at' => now()]);
            foreach ($view['items'] as $i) {
                $o = $offers[$i['offer_id']];
                abort_unless($o->stock_quantity >= $i['quantity'], 409, 'Insufficient stock.');
                DB::table('offers')->where('id', $o->id)->decrement('stock_quantity', $i['quantity']);
                $itemId = (string) Str::uuid();
                DB::table('order_items')->insert(['id' => $itemId, 'order_id' => $order, 'offer_id' => $o->id, 'product_id' => $o->product_id, 'seller_id' => $o->seller_id, 'quantity' => $i['quantity'], 'product_snapshot' => json_encode([...$i['product'], ...(array) DB::table('products')->where('id', $o->product_id)->first(['edition', 'gift_face_amount_minor', 'gift_face_currency', 'subscription_duration_months'])]), 'seller_name_snapshot' => $i['seller']['name'], 'base_unit_eur_minor' => $o->price_eur_minor, 'charged_unit_minor' => $i['unit_price']['minor'], 'line_total_minor' => $i['line_total']['minor']]);
                for ($unit = 1; $unit <= $i['quantity']; $unit++) {
                    $value = 'DEMO-'.strtoupper((string) Str::uuid());
                    DB::table('order_keys')->insert(['id' => (string) Str::uuid(), 'order_item_id' => $itemId, 'unit_number' => $unit, 'key_ciphertext' => Crypt::encryptString($value), 'key_digest' => hash('sha256', $value), 'created_at' => now()]);
                }
            }
            DB::table('order_email_deliveries')->insert(['id' => (string) Str::uuid(), 'order_id' => $order, 'status' => 'pending', 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('cart_items')->where('cart_id', $cart->id)->delete();
            $this->carts->touch($cart);

            return $finish('succeeded', 201, ['data' => ['attempt_id' => $attempt, 'status' => 'succeeded', 'order_id' => $order, 'order_number' => $number]]);
        }, 3);
    }
}
