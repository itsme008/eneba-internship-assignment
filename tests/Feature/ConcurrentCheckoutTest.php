<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Cart;
use App\Services\Checkout;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConcurrentCheckoutTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_buyers_cannot_both_purchase_the_last_key(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl is required for the process contention test.');
        }
        $this->seed(DatabaseSeeder::class);
        $offer = DB::table('offers')->first();
        DB::table('offers')->where('id', $offer->id)->update(['stock_quantity' => 1]);
        $buyers = [];
        $quotes = [];
        for ($i = 0; $i < 2; $i++) {
            $u = User::create(['email' => Str::uuid().'@example.com', 'display_name' => 'Concurrent buyer', 'password' => 'a-long-test-password']);
            $buyers[] = $u->id;
            app(Cart::class)->set($u->id, $offer->id, 1, '0');
            $quotes[] = app(Checkout::class)->quote($u->id, 'EUR', '1')['quote_token'];
        }
        // Separate PostgreSQL connections and a shared start time exercise actual row-lock contention.
        $files = [tempnam(sys_get_temp_dir(), 'eneba-test-'), tempnam(sys_get_temp_dir(), 'eneba-test-')];
        $start = microtime(true) + .15;
        $pids = [];
        DB::disconnect();
        for ($i = 0; $i < 2; $i++) {
            $pid = pcntl_fork();
            if ($pid === 0) {
                DB::purge();
                while (microtime(true) < $start) {
                    usleep(1000);
                }try {
                    $result = app(Checkout::class)->purchase($buyers[$i], (string) Str::uuid(), $quotes[$i], 'card', false, 1);
                    file_put_contents($files[$i], json_encode(['http' => $result['http']]));
                } catch (\Throwable $e) {
                    file_put_contents($files[$i], json_encode(['error' => $e->getMessage()]));
                }exit(0);
            } $pids[] = $pid;
        }
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
        }
        DB::purge();
        $results = array_map(fn ($f) => json_decode(file_get_contents($f), true), $files);
        foreach ($files as $f) {
            unlink($f);
        }
        $statuses = array_column($results, 'http');
        sort($statuses);
        $this->assertSame([201, 409], $statuses, json_encode($results));
        $this->assertSame(0, DB::table('offers')->where('id', $offer->id)->value('stock_quantity'));
        $this->assertSame(1, DB::table('orders')->count());
        $this->assertSame(1, DB::table('order_keys')->count());
        $this->assertSame(1, DB::table('cart_items')->count());
    }
}
