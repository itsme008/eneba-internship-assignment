<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Cart;
use App\Services\Checkout;
use App\Services\Money;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function buyer(): User
    {
        return User::create(['email' => Str::uuid().'@example.com', 'display_name' => 'Test Buyer', 'password' => Hash::make('a-long-test-password')]);
    }

    private function offer(): object
    {
        return DB::table('offers')->orderBy('id')->first();
    }

    private function filled(User $u, int $qty = 2): array
    {
        $o = $this->offer();
        app(Cart::class)->set($u->id, $o->id, $qty, '0');

        return [$o, app(Checkout::class)->quote($u->id, 'USD', '1')];
    }

    public function test_keys_library_is_owner_scoped_searchable_and_contains_no_key_values(): void
    {
        $this->getJson('/api/v1/library')->assertUnauthorized();
        $u = $this->buyer();
        [, $quote] = $this->filled($u);
        $result = app(Checkout::class)->purchase($u->id, (string) Str::uuid(), $quote['quote_token'], 'card', false, 1);
        $id = $result['body']['data']['order_id'];
        $orderNumber = DB::table('orders')->where('id', $id)->value('order_number');
        $response = $this->actingAs($u)->withSession(['auth_version' => 1])->getJson('/api/v1/library')->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.order_id', $id)->assertJsonPath('data.0.quantity', 2);
        $this->assertArrayNotHasKey('keys', $response->json('data.0'));
        $this->assertStringNotContainsString('DEMO-', $response->getContent());
        $title = $response->json('data.0.product.title');
        $this->getJson('/api/v1/library?q='.urlencode(mb_strtolower($title)))->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/library?q='.urlencode($orderNumber))->assertOk()->assertJsonPath('meta.total', 1);
        $typo = mb_substr($title, 0, 3).mb_substr($title, 4);
        $this->getJson('/api/v1/library?q='.urlencode($typo))->assertOk()->assertJsonPath('data.0.order_id', $id);
        $this->getJson('/api/v1/library?q=%25')->assertOk()->assertJsonPath('meta.total', 0);

        $this->getJson('/api/v1/library?q=not-a-purchased-product')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/library?page=0')->assertUnprocessable();
        $this->actingAs($this->buyer())->withSession(['auth_version' => 1])->getJson('/api/v1/library?q='.urlencode($orderNumber))->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/library?q='.urlencode($typo))->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_seed_counts_offers_and_repeat_seed_are_stable(): void
    {
        $this->assertSame(30, DB::table('products')->where('product_type', 'game')->count());
        $this->assertSame(15, DB::table('products')->where('product_type', 'gift_card')->count());
        $this->assertSame(9, DB::table('products')->where('product_type', 'subscription')->count());
        $counts = DB::table('offers')->selectRaw('product_id,count(*) AS total,count(DISTINCT seller_id) AS sellers')->groupBy('product_id')->get();
        foreach ($counts as $c) {
            $this->assertContains($c->total, [6, 7]);
            $this->assertSame($c->total, $c->sellers);
        }
        $before = DB::table('offers')->orderBy('id')->get()->toJson();
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($before, DB::table('offers')->orderBy('id')->get()->toJson());
    }

    public function test_catalog_filters_sorts_pagination_and_typo_search(): void
    {
        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(12, 'data')->assertJsonPath('meta.total', 54)->assertJsonPath('meta.last_page', 5);
        $this->getJson('/api/v1/products?type[]=gift_card&type[]=subscription')->assertOk()->assertJsonPath('meta.total', 24);
        $this->getJson('/api/v1/products?type[]=gift_card&genre[]=action')->assertOk()->assertJsonPath('meta.total', 0);
        $r = $this->getJson('/api/v1/products?sort=price_asc&currency=GBP')->assertOk();
        $prices = array_map(fn ($x) => (int) $x['starting_price']['minor'], $r->json('data'));
        $sorted = $prices;
        sort($sorted);
        $this->assertSame($sorted, $prices);
        $this->getJson('/api/v1/products?q=mincraft')->assertOk()->assertJsonPath('data.0.title', 'Minecraft');
        $this->getJson('/api/v1/search/suggestions?q=mine')->assertOk()->assertJsonPath('data.items.0.title', 'Minecraft');
        $single = $this->getJson('/api/v1/search/suggestions?q=x')->assertOk()->json('data.items');
        $this->assertNotEmpty($single);
        foreach ($single as $item) {
            $this->assertStringContainsString('x', mb_strtolower($item['title']));
        }
        $this->getJson('/api/v1/search/suggestions?q=cybrpunk')->assertOk()->assertJsonPath('data.items.0.title', 'Cyberpunk 2077');
        $this->getJson('/api/v1/search/suggestions?q=%20')->assertOk()->assertJsonCount(0, 'data.items');

        $this->getJson('/api/v1/products?sort=madeup')->assertUnprocessable();
        $this->postJson('/api/v1/cart/preview', ['items' => []])->assertOk()->assertJsonPath('data.can_checkout', false);
        $this->postJson('/api/v1/cart/preview', ['items' => [], 'total_minor' => '1'])->assertUnprocessable();
    }

    public function test_unit_conversion_rounds_before_quantity(): void
    {
        $this->assertSame(111, app(Money::class)->convert(101, 'USD'));
        $u = $this->buyer();
        $o = $this->offer();
        DB::table('offers')->where('id', $o->id)->update(['price_eur_minor' => 101]);
        app(Cart::class)->set($u->id, $o->id, 3, '0');
        $this->assertSame('333', app(Cart::class)->read($u->id, 'USD')['total']['minor']);
    }

    public function test_success_replay_unique_encrypted_keys_and_owner_only_retrieval(): void
    {
        $u = $this->buyer();
        [$o,$q] = $this->filled($u);
        $key = (string) Str::uuid();
        $checkout = app(Checkout::class);
        $a = $checkout->purchase($u->id, $key, $q['quote_token'], 'card', false, 1);
        $this->assertSame(201, $a['http']);
        $this->travel(20)->minutes();
        $b = $checkout->purchase($u->id, $key, $q['quote_token'], 'card', false, 1);
        $this->assertTrue($b['replayed']);
        $this->assertEquals($a['body'], $b['body']);
        $this->assertSame(1, DB::table('orders')->count());
        $this->assertSame($o->stock_quantity - 2, DB::table('offers')->where('id', $o->id)->value('stock_quantity'));
        $this->assertSame(2, DB::table('order_keys')->count());
        $values = DB::table('order_keys')->get()->map(fn ($k) => Crypt::decryptString($k->key_ciphertext));
        $this->assertCount(2, $values->unique());
        $this->assertStringStartsWith('DEMO-', $values[0]);
        $this->assertSame([], app(Cart::class)->read($u->id, 'USD')['items']);
        $id = $a['body']['data']['order_id'];
        $this->actingAs($u)->withSession(['auth_version' => 1])->getJson('/api/v1/orders/'.$id)->assertOk()->assertJsonCount(2, 'data.items.0.keys');
        $this->actingAs($this->buyer())->withSession(['auth_version' => 1])->getJson('/api/v1/orders/'.$id)->assertNotFound();
    }

    public function test_decline_and_stock_conflict_have_no_order_or_cart_effects(): void
    {
        $u = $this->buyer();
        [$o,$q] = $this->filled($u);
        $a = app(Checkout::class)->purchase($u->id, (string) Str::uuid(), $q['quote_token'], 'paypal', true, 1);
        $this->assertSame('declined', $a['body']['data']['status']);
        $this->assertSame($o->stock_quantity, DB::table('offers')->where('id', $o->id)->value('stock_quantity'));
        DB::table('offers')->where('id', $o->id)->update(['stock_quantity' => 1]);
        $a = app(Checkout::class)->purchase($u->id, (string) Str::uuid(), $q['quote_token'], 'card', false, 1);
        $this->assertSame(409, $a['http']);
        $this->assertSame(0, DB::table('orders')->count());
        $this->assertSame(0, DB::table('order_keys')->count());
        $this->assertSame(2, DB::table('cart_items')->value('quantity'));
    }

    public function test_merge_replay_preserves_shortage_and_revision_conflicts(): void
    {
        $u = $this->buyer();
        $o = $this->offer();
        $cart = app(Cart::class);
        $cart->set($u->id, $o->id, 1, '0');
        $transfer = (string) Str::uuid();
        $items = [['offer_id' => $o->id, 'quantity' => $o->stock_quantity]];
        $this->assertFalse($cart->merge($u->id, $transfer, $items));
        $this->assertTrue($cart->merge($u->id, $transfer, $items));
        $view = $cart->read($u->id, 'EUR');
        $this->assertSame($o->stock_quantity + 1, $view['items'][0]['quantity']);
        $this->assertFalse($view['can_checkout']);
        $this->assertSame('2', $view['revision']);
        $this->actingAs($u)->withSession(['auth_version' => 1])->putJson('/api/v1/cart/items/'.$o->id, ['quantity' => 1, 'expected_revision' => '1'])->assertConflict();
    }

    public function test_quote_tampering_expiry_and_idempotency_mismatch(): void
    {
        $u = $this->buyer();
        [$o,$q] = $this->filled($u);
        $key = (string) Str::uuid();
        $checkout = app(Checkout::class);
        $checkout->purchase($u->id, $key, $q['quote_token'], 'card', true, 1);
        $this->actingAs($u)->withSession(['auth_version' => 1])->postJson('/api/v1/checkout/attempts', ['quote_token' => $q['quote_token'], 'payment_method' => 'paypal', 'simulate_failure' => false], ['Idempotency-Key' => $key])->assertConflict();
        $this->postJson('/api/v1/checkout/attempts', ['quote_token' => 'forged', 'payment_method' => 'card', 'simulate_failure' => false], ['Idempotency-Key' => (string) Str::uuid()])->assertUnprocessable();
        $this->travel(11)->minutes();
        $a = $checkout->purchase($u->id, (string) Str::uuid(), $q['quote_token'], 'card', false, 1);
        $this->assertSame('QUOTE_EXPIRED', $a['body']['error']['code']);
        $this->assertSame(0, DB::table('orders')->count());
    }

    public function test_registration_session_identity_and_password_revocation(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/register', ['email' => 'new@example.com', 'display_name' => 'New Buyer', 'password' => 'a-long-test-password', 'password_confirmation' => 'a-long-test-password'])->assertCreated()->assertJsonPath('data.display_name', 'New Buyer');
        $this->getJson('/api/v1/me')->assertOk();
        $this->patchJson('/api/v1/me', ['display_name' => 'Changed'])->assertOk()->assertJsonPath('data.display_name', 'Changed');
        $this->postJson('/api/v1/me/password', ['current_password' => 'a-long-test-password', 'password' => 'a-new-long-password', 'password_confirmation' => 'a-new-long-password'])->assertNoContent();
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->assertSame(2, DB::table('users')->where('email', 'new@example.com')->value('auth_version'));
    }

    public function test_unexpected_key_write_failure_rolls_back_entire_purchase(): void
    {
        $u = $this->buyer();
        [$o,$q] = $this->filled($u);
        $triggered = false;
        DB::listen(function ($query) use (&$triggered) {
            if (str_starts_with($query->sql, 'insert into "order_keys"') && ! $triggered) {
                $triggered = true;
                throw new \RuntimeException('Injected key write failure');
            }
        });
        try {
            app(Checkout::class)->purchase($u->id, (string) Str::uuid(), $q['quote_token'], 'digital_wallet', false, 1);
            $this->fail('Expected write failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Injected key write failure', $e->getMessage());
        }
        $this->assertTrue($triggered);
        $this->assertSame(0, DB::table('orders')->count());
        $this->assertSame(0, DB::table('order_items')->count());
        $this->assertSame(0, DB::table('order_keys')->count());
        $this->assertSame(0, DB::table('checkout_attempts')->count());
        $this->assertSame($o->stock_quantity, DB::table('offers')->where('id', $o->id)->value('stock_quantity'));
        $this->assertSame(2, DB::table('cart_items')->value('quantity'));
    }

    public function test_changed_price_and_stale_session_reject_without_purchase(): void
    {
        $u = $this->buyer();
        [$o,$q] = $this->filled($u);
        DB::table('offers')->where('id', $o->id)->increment('price_eur_minor');
        $a = app(Checkout::class)->purchase($u->id, (string) Str::uuid(), $q['quote_token'], 'card', false, 1);
        $this->assertSame('QUOTE_CHANGED', $a['body']['error']['code']);
        $this->assertSame(0, DB::table('orders')->count());
        DB::table('users')->where('id', $u->id)->update(['auth_version' => 2]);
        $this->actingAs($u->fresh())->withSession(['auth_version' => 1])->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_homepage_blocks_are_backend_driven_and_disabled_independently(): void
    {
        $this->getJson('/api/v1/homepage/sections')->assertOk()->assertJsonCount(8, 'data');
        DB::table('content_blocks')->where('key', 'hero')->update(['title' => 'Changed in backend', 'configuration' => json_encode(['eyebrow' => 'Backend copy'])]);
        $this->getJson('/api/v1/homepage/sections/hero')->assertOk()->assertJsonPath('data.title', 'Changed in backend')->assertJsonPath('data.configuration.eyebrow', 'Backend copy');
        DB::table('content_blocks')->where('key', 'hero')->update(['is_enabled' => false]);
        $this->getJson('/api/v1/homepage/sections/hero')->assertNotFound();
        $this->getJson('/api/v1/homepage/sections/featured')->assertOk()->assertJsonCount(5, 'data.items');
        DB::table('content_blocks')->where('key', 'shortcuts')->update(['configuration' => json_encode(['links' => [['title' => 'Backend navigation', 'type' => 'game']]])]);
        $this->getJson('/api/v1/homepage/sections/shortcuts')->assertOk()->assertJsonPath('data.configuration.links.0.title', 'Backend navigation')->assertJsonPath('data.configuration.links.0.url', '/catalog?type=game');
        $this->getJson('/api/v1/homepage/sections/genres')->assertOk()->assertJsonCount(6, 'data.items')->assertJsonPath('data.renderer_type', 'genres');

    }
}
