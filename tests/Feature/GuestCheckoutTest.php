<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Cart;
use App\Services\Checkout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['orders.email_driver' => 'disabled']);
        Http::preventStrayRequests();
    }

    private function buyer(): User
    {
        return User::factory()->create(['password' => 'a-strong-demo-password']);
    }

    private function quote(User $user): array
    {
        $offer = DB::table('offers')->first();
        app(Cart::class)->set($user->id, $offer->id, 2, '0');
        $quote = app(Checkout::class)->quote($user->id, 'EUR', '1');

        return [$offer, $quote['quote_token']];
    }

    private function buy(string $token, ?string $key = null, bool $fail = false)
    {
        return $this->postJson('/api/v1/checkout/attempts', ['quote_token' => $token, 'payment_method' => 'card', 'simulate_failure' => $fail], ['Idempotency-Key' => $key ?? (string) Str::uuid()]);
    }

    // Fixtures represent already-existing guest purchases. New guest payment is disabled.
    private function historicalOrder(string $email = 'legacy@example.com'): array
    {
        $user = $this->buyer();
        [, $token] = $this->quote($user);
        $result = app(Checkout::class)->purchase($user->id, (string) Str::uuid(), $token, 'card', false, 1);
        $id = $result['body']['data']['order_id'];
        $guest = (string) Str::uuid();
        DB::table('guest_checkouts')->insert(['id' => $guest, 'email' => $email, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('orders')->where('id', $id)->update(['user_id' => null, 'guest_checkout_id' => $guest, 'buyer_email_snapshot' => $email]);

        return [$id, $guest];
    }

    public function test_guest_cannot_quote_or_pay_even_with_a_valid_quote_or_historical_session(): void
    {
        $user = $this->buyer();
        [$offer, $token] = $this->quote($user);
        [$oldOrder, $guest] = $this->historicalOrder();
        $before = DB::table('orders')->count();
        $stock = DB::table('offers')->where('id', $offer->id)->value('stock_quantity');
        $this->withSession(['guest_checkout_id' => $guest])->postJson('/api/v1/checkout/quote', ['email' => 'guest@example.com', 'items' => [['offer_id' => $offer->id, 'quantity' => 1]], 'currency' => 'EUR'])->assertUnauthorized();
        $this->buy($token)->assertUnauthorized();
        $this->assertSame($before, DB::table('orders')->count());
        $this->assertSame($stock, DB::table('offers')->where('id', $offer->id)->value('stock_quantity'));
        Http::assertNothingSent();
        $this->getJson('/api/v1/checkout/orders/'.$oldOrder)->assertOk()->assertJsonCount(2, 'data.items.0.keys');
    }

    public function test_service_rejects_guest_checkout_even_without_route_middleware(): void
    {
        foreach ([fn () => app(Checkout::class)->quote(null, 'EUR', '0', (string) Str::uuid()), fn () => app(Checkout::class)->purchase(null, (string) Str::uuid(), 'old-guest-token', 'card', false, 1, (string) Str::uuid())] as $action) {
            try {
                $action();
                $this->fail('Guest checkout should be rejected.');
            } catch (HttpException $e) {
                $this->assertSame(401, $e->getStatusCode());
            }
        }
    }

    public function test_legacy_guest_order_access_and_registration_claim_remain_private(): void
    {
        [$id, $guest] = $this->historicalOrder();
        [$other] = $this->historicalOrder();
        $this->withSession(['guest_checkout_id' => (string) Str::uuid()])->getJson('/api/v1/checkout/orders/'.$id)->assertNotFound();
        $this->withSession(['guest_checkout_id' => $guest])->postJson('/api/v1/auth/register', ['email' => 'legacy@example.com', 'display_name' => 'Legacy Buyer', 'password' => 'a-strong-demo-password', 'password_confirmation' => 'a-strong-demo-password', 'claim_order_id' => $id])->assertCreated();
        $this->getJson('/api/v1/orders')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $id);
        $this->assertNull(DB::table('orders')->where('id', $other)->value('user_id'));
        $this->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->withSession(['guest_checkout_id' => $guest])->getJson('/api/v1/checkout/orders/'.$id)->assertNotFound();
    }

    public function test_legacy_claim_requires_session_proof_and_matching_email(): void
    {
        [$id, $guest] = $this->historicalOrder();
        $before = DB::table('users')->count();
        $body = ['email' => 'wrong@example.com', 'display_name' => 'Buyer', 'password' => 'a-strong-demo-password', 'password_confirmation' => 'a-strong-demo-password', 'claim_order_id' => $id];
        $this->withSession(['guest_checkout_id' => $guest])->postJson('/api/v1/auth/register', $body)->assertUnprocessable();
        $body['email'] = 'legacy@example.com';
        $this->withSession(['guest_checkout_id' => (string) Str::uuid()])->postJson('/api/v1/auth/register', $body)->assertNotFound();
        $this->assertSame($before, DB::table('users')->count());
    }

    public function test_signed_in_buyer_receives_email_and_retry_never_repurchases(): void
    {
        config(['orders.email_driver' => 'brevo', 'orders.brevo_api_key' => 'fake-test-key', 'mail.from.address' => 'sender@example.com']);
        Http::fake(['api.brevo.com/*' => Http::sequence()->push(['message' => 'provider unavailable'], 503)->push(['messageId' => 'test-id'], 201)]);
        $user = $this->buyer();
        [$offer, $token] = $this->quote($user);
        $this->actingAs($user)->withSession(['auth_version' => 1]);
        $this->postJson('/api/v1/checkout/quote', ['currency' => 'EUR', 'expected_revision' => 1, 'email' => 'other@example.com'])->assertUnprocessable();
        $this->postJson('/api/v1/checkout/quote', ['currency' => 'EUR', 'expected_revision' => 1])->assertOk();
        $key = (string) Str::uuid();
        $id = $this->buy($token, $key)->assertCreated()->json('data.order_id');
        $this->getJson('/api/v1/orders/'.$id)->assertOk()->assertJsonPath('data.is_guest_order', false)->assertJsonPath('data.email_delivery.status', 'failed');
        $this->travel(31)->seconds();
        $this->postJson('/api/v1/checkout/orders/'.$id.'/email')->assertOk()->assertJsonPath('data.status', 'sent');
        Http::assertSent(fn ($r) => $r['to'][0]['email'] === $user->email && str_contains($r['htmlContent'], 'DEMO-'));
        $this->buy($token, $key)->assertCreated();
        $this->assertSame(1, DB::table('orders')->count());
        $this->assertSame(2, DB::table('order_keys')->count());
        $this->assertSame($offer->stock_quantity - 2, DB::table('offers')->where('id', $offer->id)->value('stock_quantity'));
        $this->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->buy($token, $key)->assertUnauthorized();
        $this->getJson('/api/v1/checkout/orders/'.$id)->assertNotFound();
    }

    public function test_signed_in_decline_leaves_stock_cart_and_email_unchanged(): void
    {
        Http::fake();
        $user = $this->buyer();
        [$offer, $token] = $this->quote($user);
        $this->actingAs($user)->withSession(['auth_version' => 1]);
        $this->buy($token, null, true)->assertOk()->assertJsonPath('data.status', 'declined');
        $this->assertSame(0, DB::table('orders')->count());
        $this->assertSame($offer->stock_quantity, DB::table('offers')->where('id', $offer->id)->value('stock_quantity'));
        $this->assertCount(1, app(Cart::class)->read($user->id, 'EUR')['items']);
        Http::assertNothingSent();
    }
}
