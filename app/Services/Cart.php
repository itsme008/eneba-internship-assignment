<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Cart
{
    public function __construct(public Catalog $catalog, public Money $money) {}

    public function account(string $userId): object
    {
        DB::table('carts')->insertOrIgnore(['id' => (string) Str::uuid(), 'user_id' => $userId, 'revision' => 0, 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('carts')->where('user_id', $userId)->first();
    }

    public function render(?object $cart, array $items, string $currency): array
    {
        $lines = [];
        $issues = [];
        $total = 0;
        $priceable = true;
        foreach ($items as $item) {
            $id = is_array($item) ? $item['offer_id'] : $item->offer_id;
            $quantity = (int) (is_array($item) ? $item['quantity'] : $item->quantity);
            $o = DB::table('offers as o')->join('sellers as s', 's.id', '=', 'o.seller_id')->join('products as p', 'p.id', '=', 'o.product_id')
                ->where('o.id', $id)->first(['o.*', 's.display_name', 's.is_active as seller_active', 'p.is_active as product_active']);
            if (! $o) {
                $issues[] = ['offer_id' => $id, 'code' => 'OFFER_UNAVAILABLE'];
                $priceable = false;

                continue;
            }
            $unit = $this->money->convert($o->price_eur_minor, $currency);
            $line = $unit * $quantity;
            $total += $line;
            $valid = $o->is_active && $o->seller_active && $o->product_active && $quantity <= $o->stock_quantity;
            if (! $valid) {
                $issues[] = ['offer_id' => $id, 'code' => 'INSUFFICIENT_STOCK', 'available_quantity' => $o->stock_quantity];
            }
            $product = $this->catalog->query()->where('p.id', $o->product_id)->first();
            $lines[] = ['offer_id' => $id, 'product' => $product ? $this->catalog->card($product, $currency) : ['title' => 'Unavailable product', 'slug' => '', 'image' => ['url' => '/images/fallback.svg']],
                'seller' => ['id' => $o->seller_id, 'name' => $o->display_name], 'quantity' => $quantity, 'available_quantity' => $o->stock_quantity,
                'unit_price' => $this->money->value($unit, $currency), 'line_total' => $this->money->value($line, $currency), 'is_purchasable' => $valid];
        }

        return ['id' => $cart?->id, 'revision' => $cart ? (string) $cart->revision : null, 'currency' => $currency, 'fx_version' => $this->money->rate($currency)->version,
            'items' => $lines, 'issues' => $issues, 'can_checkout' => count($lines) > 0 && ! $issues, 'subtotal' => $priceable ? $this->money->value($total, $currency) : null, 'total' => $priceable ? $this->money->value($total, $currency) : null];
    }

    public function read(string $userId, string $currency): array
    {
        $cart = $this->account($userId);

        return $this->render($cart, DB::table('cart_items')->where('cart_id', $cart->id)->orderBy('offer_id')->get()->all(), $currency);
    }

    public function locked(string $userId): object
    {
        DB::table('users')->where('id', $userId)->lockForUpdate()->first();
        $cart = $this->account($userId);

        return DB::table('carts')->where('id', $cart->id)->lockForUpdate()->first();
    }

    public function guestAccount(string $guest): object
    {
        DB::table('carts')->insertOrIgnore(['id' => (string) Str::uuid(), 'guest_checkout_id' => $guest, 'revision' => 0, 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('carts')->where('guest_checkout_id', $guest)->first();
    }

    public function lockedGuest(string $guest): object
    {
        abort_unless(DB::table('guest_checkouts')->where('id', $guest)->lockForUpdate()->first(), 404);
        $cart = $this->guestAccount($guest);

        return DB::table('carts')->where('id', $cart->id)->lockForUpdate()->first();
    }

    public function readGuest(string $guest, string $currency): array
    {
        $cart = $this->guestAccount($guest);

        return $this->render($cart, DB::table('cart_items')->where('cart_id', $cart->id)->orderBy('offer_id')->get()->all(), $currency);
    }

    public function revision(object $cart, string $expected): void
    {
        abort_unless((string) $cart->revision === $expected, 409, 'Your cart changed. Please review it again.');
    }

    public function touch(object $cart): void
    {
        DB::table('carts')->where('id', $cart->id)->increment('revision', 1, ['updated_at' => now()]);
    }

    public function set(string $userId, string $offerId, int $quantity, string $expected): void
    {
        DB::transaction(function () use ($userId, $offerId, $quantity, $expected) {
            $cart = $this->locked($userId);
            $this->revision($cart, $expected);
            if ($quantity === 0) {
                if (DB::table('cart_items')->where('cart_id', $cart->id)->where('offer_id', $offerId)->delete()) {
                    $this->touch($cart);
                }

                return;
            }
            $o = DB::table('offers')->where('id', $offerId)->first();
            abort_unless($o, 404, 'Offer not found.');
            abort_unless($o->is_active && $o->stock_quantity >= $quantity, 409, 'Not enough stock for that quantity.');
            $old = DB::table('cart_items')->where('cart_id', $cart->id)->where('offer_id', $offerId)->first();
            if ($old && $old->quantity === $quantity) {
                return;
            }
            abort_if(! $old && DB::table('cart_items')->where('cart_id', $cart->id)->count() >= 400, 422, 'Your cart has reached its item limit.');
            DB::table('cart_items')->updateOrInsert(['cart_id' => $cart->id, 'offer_id' => $offerId], ['id' => $old?->id ?: (string) Str::uuid(), 'quantity' => $quantity]);
            $this->touch($cart);
        }, 3);
    }

    public function merge(string $userId, string $transfer, array $items): bool
    {
        usort($items, fn ($a, $b) => strcmp($a['offer_id'], $b['offer_id']));
        $hash = hash('sha256', json_encode($items));

        return DB::transaction(function () use ($userId, $transfer, $items, $hash) {
            $cart = $this->locked($userId);
            $receipt = DB::table('cart_merges')->where('user_id', $userId)->where('transfer_id', $transfer)->first();
            if ($receipt) {
                abort_unless(hash_equals($receipt->payload_hash, $hash), 409, 'Transfer identity was reused with different contents.');

                return true;
            }
            foreach ($items as $item) {
                abort_unless(DB::table('offers')->where('id', $item['offer_id'])->exists(), 422, 'A guest offer no longer exists. Remove it and retry.');
                $old = DB::table('cart_items')->where('cart_id', $cart->id)->where('offer_id', $item['offer_id'])->first();
                $qty = ($old?->quantity ?? 0) + $item['quantity'];
                abort_if($qty > 10000, 422, 'Merged quantity is too large.');
                DB::table('cart_items')->updateOrInsert(['cart_id' => $cart->id, 'offer_id' => $item['offer_id']], ['id' => $old?->id ?: (string) Str::uuid(), 'quantity' => $qty]);
            }
            abort_if(DB::table('cart_items')->where('cart_id', $cart->id)->count() > 400, 422, 'Merged cart exceeds its item limit.');
            if ($items) {
                $this->touch($cart);
            }
            DB::table('cart_merges')->insert(['id' => (string) Str::uuid(), 'user_id' => $userId, 'cart_id' => $cart->id, 'transfer_id' => $transfer, 'payload_hash' => $hash, 'applied_revision' => $cart->revision + (int) (bool) $items, 'merged_at' => now()]);

            return false;
        }, 3);
    }
}
