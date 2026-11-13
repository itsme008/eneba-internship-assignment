<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // A repeat run never overwrites purchased stock, keys or customer data.
        if (DB::table('products')->exists()) {
            return;
        }
        DB::transaction(function () {
            $id = fn () => (string) Str::uuid();
            $now = now();
            $tax = [];
            foreach (['platforms' => ['steam' => 'Steam', 'playstation' => 'PlayStation', 'xbox' => 'Xbox'], 'regions' => ['global' => 'Global', 'europe' => 'Europe', 'us' => 'United States'], 'genres' => ['action' => 'Action', 'adventure' => 'Adventure', 'rpg' => 'RPG', 'simulation' => 'Simulation', 'strategy' => 'Strategy', 'indie' => 'Indie']] as $table => $values) {
                foreach ($values as $code => $name) {
                    $tax[$table][$code] = $id();
                    DB::table($table)->insert(['id' => $tax[$table][$code], 'code' => $code, 'name' => $name]);
                }
            }
            $sellers = [];
            foreach (['KeyGarden', 'Pixel Supply', 'Game Harbour', 'Level Up Keys', 'Nova Games', 'Digital Vault', 'Play Republic', 'Arcade Market'] as $i => $name) {
                $sellers[] = $sid = $id();
                DB::table('sellers')->insert(['id' => $sid, 'slug' => Str::slug($name), 'display_name' => $name, 'rating_score' => 9.15 + $i * .1, 'is_active' => true]);
            }
            $games = [['Elden Ring', 1245620], ['Cyberpunk 2077', 1091500], ['Hogwarts Legacy', 990080], ['Red Dead Redemption 2', 1174180], ['Baldur’s Gate 3', 1086940], ['God of War', 1593500], ['Forza Horizon 5', 1551360], ['Hades', 1145360], ['Hollow Knight', 367520], ['Stardew Valley', 413150], ['Minecraft', 0], ['Grand Theft Auto V', 271590], ['The Witcher 3: Wild Hunt', 292030], ['No Man’s Sky', 275850], ['DOOM Eternal', 782330], ['Sekiro: Shadows Die Twice', 814380], ['Resident Evil 4', 2050650], ['It Takes Two', 1426210], ['Portal 2', 620], ['Terraria', 105600], ['Sid Meier’s Civilization VI', 289070], ['Age of Empires IV', 1466860], ['Cities: Skylines', 255710], ['Euro Truck Simulator 2', 227300], ['Sea of Thieves', 1172620], ['Halo Infinite', 1240440], ['Death Stranding', 1850570], ['Disco Elysium', 632470], ['Dead Cells', 588650], ['Celeste', 504230]];
            $all = [];
            $byType = ['game' => [], 'gift_card' => [], 'subscription' => []];
            for ($n = 0; $n < 54; $n++) {
                $type = $n < 30 ? 'game' : ($n < 45 ? 'gift_card' : 'subscription');
                $platform = array_keys($tax['platforms'])[$n % 3];
                $region = array_keys($tax['regions'])[intdiv($n, 3) % 3];
                $face = null;
                $duration = null;
                if ($type === 'game') {
                    [$title,$steam] = $games[$n];
                    $image = $steam ? "/images/games/$steam.jpg" : '/images/minecraft.svg';
                } elseif ($type === 'gift_card') {
                    $face = [1000, 2000, 2500, 5000, 10000][intdiv($n - 30, 3)];
                    $title = ['steam' => 'Steam Wallet', 'playstation' => 'PlayStation Store', 'xbox' => 'Xbox Gift Card'][$platform].' €'.($face / 100);
                    $image = "/images/$platform-card.svg";
                } else {
                    $duration = [1, 3, 12][intdiv($n - 45, 3)];
                    $title = ['steam' => 'PC Game Membership', 'playstation' => 'PlayStation Plus Essential', 'xbox' => 'Xbox Game Pass Ultimate'][$platform]." — $duration ".($duration === 1 ? 'month' : 'months');
                    $image = "/images/$platform-sub.svg";
                }
                $pid = $id();
                $all[] = $pid;
                $byType[$type][] = $pid;
                DB::table('products')->insert(['id' => $pid, 'slug' => Str::slug($title).'-'.$region, 'title' => $title, 'product_type' => $type, 'platform_id' => $tax['platforms'][$platform], 'region_id' => $tax['regions'][$region],
                    'description' => ($type === 'game' ? "Discover $title and find your next favourite adventure. Explore memorable worlds, sharpen your skills and make every session your own." : ($type === 'gift_card' ? 'Top up your gaming balance and choose something you love. Redeem this prepaid digital code on the listed platform.' : 'Unlock a new collection of experiences with a prepaid membership code. This demo subscription is a one-time digital key and does not renew.')).' Check the platform and activation region before purchasing. All offers, availability and reviews in this internship demo are sample data.',
                    'edition' => $type === 'game' ? 'Standard edition' : null, 'gift_face_amount_minor' => $face, 'gift_face_currency' => $face ? 'EUR' : null, 'subscription_duration_months' => $duration, 'popularity_score' => 1000 - $n * 13, 'demo_cashback_percent' => 5 + $n % 8, 'is_active' => true, 'catalog_added_at' => $now->copy()->subDays($n * 2), 'created_at' => $now, 'updated_at' => $now]);
                DB::table('product_images')->insert(['id' => $id(), 'product_id' => $pid, 'asset_path' => $image, 'alt_text' => $title.' cover', 'position' => 0]);
                if ($type === 'game') {
                    foreach (array_unique([array_keys($tax['genres'])[$n % 6], array_keys($tax['genres'])[($n + 1) % 6]]) as $genre) {
                        DB::table('product_genres')->insert(['product_id' => $pid, 'genre_id' => $tax['genres'][$genre]]);
                    }
                    if ($steam) {
                        for ($j = 1; $j <= 3; $j++) {
                            DB::table('product_images')->insert(['id' => $id(), 'product_id' => $pid, 'asset_path' => "/images/games/$steam-scene-$j.jpg", 'alt_text' => "$title screenshot $j", 'position' => $j]);
                        }
                    }
                }
                $chosen = $sellers;
                shuffle($chosen);
                $base = $face ? max(100, $face - random_int(50, 200)) : ($duration ? $duration * 650 : random_int(499, 4999));
                for ($j = 0; $j < 6 + $n % 2; $j++) {
                    DB::table('offers')->insert(['id' => $id(), 'product_id' => $pid, 'seller_id' => $chosen[$j], 'price_eur_minor' => $base + $j * random_int(20, 95), 'compare_at_eur_minor' => $base + 1500, 'stock_quantity' => random_int(10, 30), 'is_active' => true, 'recommended_position' => $j < 2 ? $j + 1 : null, 'is_featured' => $j === 0, 'created_at' => $now, 'updated_at' => $now]);
                }
                foreach ([['Alex', 5, 'Easy to find the right offer and compare sellers.'], ['Morgan', 4, 'A useful addition to my collection. Check your activation region before buying.']] as [$author,$rating,$body]) {
                    DB::table('sample_reviews')->insert(['id' => $id(), 'product_id' => $pid, 'display_author' => $author, 'rating' => $rating, 'body' => $body, 'reviewed_at' => $now->copy()->subDays(7), 'is_sample' => true]);
                }
            }
            foreach ($all as $i => $pid) {
                for ($j = 1; $j <= 5; $j++) {
                    DB::table('related_products')->insert(['product_id' => $pid, 'related_product_id' => $all[($i + $j) % 54], 'position' => $j]);
                }
            }
            $rate = $id();
            DB::table('fx_rate_sets')->insert(['id' => $rate, 'version' => 'demo-2026-11-v1', 'base_currency' => 'EUR', 'effective_at' => $now]);
            foreach (['EUR' => '1.00000000', 'USD' => '1.10000000', 'GBP' => '0.85000000'] as $code => $value) {
                DB::table('fx_rates')->insert(['rate_set_id' => $rate, 'currency' => $code, 'rate' => $value]);
            }
            $blocks = [['hero', 'hero', 'Your next adventure starts here', [], ['heading_lines' => ['Your next', 'adventure'], 'heading_accent' => 'starts here.', 'caption' => 'Great games. A world of possibilities.', 'tagline' => 'Press play on something new.', 'eyebrow' => 'MORE PLAY. LESS PAY.', 'subtitle' => 'Discover great games, compare trusted sellers and find your next favourite.', 'cta_label' => 'Explore games', 'cta_url' => '/catalog?type=game', 'image' => '/images/games/1245620-scene-1.jpg']], ['shortcuts', 'shortcuts', 'Find your kind of play', [], ['links' => [['title' => 'Games', 'subtitle' => 'Find your next obsession', 'type' => 'game'], ['title' => 'Gaming gift cards', 'subtitle' => 'A perfect little power-up', 'type' => 'gift_card'], ['title' => 'Subscriptions', 'subtitle' => 'More play, every day', 'type' => 'subscription']]]], ['featured', 'product_rail', 'Recommended for you', array_slice($all, 0, 5), []], ['top_games', 'product_rail', 'Games worth staying in for', array_slice($byType['game'], 5, 5), []], ['gaming_gift_cards', 'product_rail', 'A little credit. Endless possibilities.', array_slice($byType['gift_card'], 0, 5), []], ['subscriptions', 'product_rail', 'Keep the good times going', array_slice($byType['subscription'], 0, 5), []], ['recently_viewed', 'product_rail', 'Recently viewed', [], []]];
            foreach ($blocks as $i => [$key,$renderer,$title,$products,$config]) {
                if ($key === 'shortcuts') {
                    $config['trust_items'] = [['icon' => 'shield', 'label' => 'Secure shopping'], ['icon' => 'key', 'label' => 'Instant digital delivery'], ['icon' => 'globe', 'label' => 'Offers from multiple sellers'], ['icon' => 'zap', 'label' => 'More play for your money']];
                }
                if ($renderer === 'product_rail') {
                    $config = [...$config, 'eyebrow' => $key === 'featured' ? 'PICKED FOR YOUR NEXT SESSION' : null, 'cta_url' => '/catalog'.($key === 'gaming_gift_cards' ? '?type=gift_card' : ($key === 'subscriptions' ? '?type=subscription' : ''))];
                }
                $bid = $id();
                DB::table('content_blocks')->insert(['id' => $bid, 'key' => $key, 'renderer_type' => $renderer, 'title' => $title, 'position' => $i, 'is_enabled' => true, 'configuration' => json_encode((object) $config)]);
                foreach ($products as $pos => $pid) {
                    DB::table('content_block_products')->insert(['block_id' => $bid, 'product_id' => $pid, 'position' => $pos]);
                }
            }
        });
    }
}
