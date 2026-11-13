<?php

namespace App\Http\Controllers;

use App\Services\Cart;
use App\Services\Catalog;
use App\Services\Checkout;
use App\Services\GuestCheckout;
use App\Services\Homepage\Banner;
use App\Services\Homepage\FeaturedProducts;
use App\Services\Homepage\GamingCards;
use App\Services\Homepage\Genres;
use App\Services\Homepage\Navigation;
use App\Services\Homepage\RecentlyViewed;
use App\Services\Homepage\Subscriptions;
use App\Services\Homepage\TopGames;
use App\Services\Money;
use App\Services\OrderDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MarketplaceController extends Controller
{
    public function __construct(public Catalog $catalog, public Cart $cart, public Checkout $checkout, public Money $money) {}

    private function currency(Request $r): string
    {
        $r->validate(['currency' => ['sometimes', Rule::in(['EUR', 'USD', 'GBP'])]]);

        return $r->input('currency', 'EUR');
    }

    private function data($data, array $meta = [])
    {
        return response()->json(['data' => $data, ...($meta ? ['meta' => $meta] : [])]);
    }

    public function bootstrap()
    {
        return $this->data(['types' => [['code' => 'game', 'name' => 'Games'], ['code' => 'gift_card', 'name' => 'Gaming gift cards'], ['code' => 'subscription', 'name' => 'Subscriptions']],
            'platforms' => DB::table('platforms')->get(), 'regions' => DB::table('regions')->get(), 'genres' => DB::table('genres')->get(),
            'settings' => ['countries' => ['Lithuania', 'United States', 'United Kingdom'], 'languages' => ['English'], 'currencies' => ['EUR', 'USD', 'GBP'], 'defaults' => ['country' => 'Lithuania', 'currency' => 'EUR', 'language' => 'English']],
            'fx' => ['version' => $this->money->rate('EUR')->version, 'is_mock' => true], 'payment_methods' => ['card', 'paypal', 'digital_wallet']]);
    }

    public function products(Request $r)
    {
        $currency = $this->currency($r);
        $r->validate(['q' => 'nullable|string|max:100', 'page' => 'sometimes|integer|min:1', 'type' => 'sometimes|array', 'type.*' => Rule::in(['game', 'gift_card', 'subscription']),
            'platform' => 'sometimes|array', 'platform.*' => 'exists:platforms,code', 'region' => 'sometimes|array', 'region.*' => 'exists:regions,code', 'genre' => 'sometimes|array', 'genre.*' => 'exists:genres,code',
            'price_min_minor' => 'nullable|integer|min:0', 'price_max_minor' => 'nullable|integer|min:0', 'sort' => ['sometimes', Rule::in(['popularity', 'relevance', 'price_asc', 'price_desc', 'title_asc', 'title_desc', 'date_asc', 'date_desc'])]]);
        $q = $this->catalog->query();
        $term = mb_strtolower(trim($r->input('q', '')));
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
        if ($term !== '') {
            $q->where(function ($x) use ($term, $escaped) {
                $x->whereRaw('lower(p.title) LIKE ?', ['%'.$escaped.'%']);
                if (mb_strlen($term) >= 3) {
                    $x->orWhereRaw('word_similarity(?, lower(p.title)) > 0.3', [$term]);
                }
            });
            $q->selectRaw('CASE WHEN lower(p.title) = ? THEN 4 WHEN lower(p.title) LIKE ? THEN 3 WHEN lower(p.title) LIKE ? THEN 2 ELSE 1 END + word_similarity(?, lower(p.title)) AS relevance', [$term, $escaped.'%', '%'.$escaped.'%', $term]);
        }
        if ($r->input('type')) {
            $q->whereIn('p.product_type', $r->input('type'));
        }
        if ($r->input('platform')) {
            $q->whereIn('pl.code', $r->input('platform'));
        }
        if ($r->input('region')) {
            $q->whereIn('r.code', $r->input('region'));
        }
        if ($r->input('genre')) {
            $q->whereExists(fn ($x) => $x->selectRaw('1')->from('product_genres as pg')->join('genres as g', 'g.id', '=', 'pg.genre_id')->whereColumn('pg.product_id', 'p.id')->whereIn('g.code', $r->input('genre')))->where('p.product_type', 'game');
        }
        $rate = (string) $this->money->rate($currency)->rate;
        foreach (['price_min_minor' => '>=', 'price_max_minor' => '<='] as $key => $op) {
            if ($r->filled($key)) {
                $q->whereRaw("round(prices.starting_price * CAST(? AS numeric)) $op CAST(? AS bigint)", [$rate, $r->input($key)]);
            }
        }
        if ($r->filled('price_min_minor') && $r->filled('price_max_minor')) {
            abort_if((int) $r->input('price_min_minor') > (int) $r->input('price_max_minor'), 422, 'Minimum price exceeds maximum.');
        }
        $sort = $r->input('sort', $term !== '' ? 'relevance' : 'popularity');
        abort_if($sort === 'relevance' && $term === '', 422, 'Relevance requires a search query.');
        match ($sort) {
            'price_asc' => $q->orderByRaw('prices.starting_price ASC NULLS LAST'), 'price_desc' => $q->orderByRaw('prices.starting_price DESC NULLS LAST'),
            'title_asc' => $q->orderByRaw('lower(p.title) ASC'),'title_desc' => $q->orderByRaw('lower(p.title) DESC'),
            'date_asc' => $q->orderBy('p.catalog_added_at'),'date_desc' => $q->orderByDesc('p.catalog_added_at'),
            'relevance' => $q->orderByDesc('relevance'),default => $q->orderByDesc('p.popularity_score')
        };
        $q->orderBy('p.id');
        $count = (clone $q)->count();
        $page = (int) $r->input('page', 1);

        return $this->data($q->offset(($page - 1) * 12)->limit(12)->get()->map(fn ($p) => $this->catalog->card($p, $currency)), ['page' => $page, 'per_page' => 12, 'total' => $count, 'last_page' => max(1, (int) ceil($count / 12)), 'sort' => $sort, 'query' => $term]);
    }

    public function suggestions(Request $r)
    {
        if (mb_strlen(trim($r->input('q', ''))) < 1) {
            return $this->data(['query' => $r->input('q', ''), 'items' => []]);
        }
        $r->merge(['sort' => 'relevance', 'page' => 1]);
        $response = $this->products($r)->getData(true);

        return $this->data(['query' => $r->input('q'), 'items' => array_slice($response['data'], 0, 6)]);
    }

    public function detail(Request $r, string $slug)
    {
        $c = $this->currency($r);
        $p = $this->catalog->query()->where('p.slug', $slug)->first();
        abort_unless($p, 404, 'Product not found.');
        $related = DB::table('related_products')->where('product_id', $p->id)->orderBy('position')->pluck('related_product_id');
        $cards = $this->catalog->query()->whereIn('p.id', $related)->get()->keyBy('id');
        $reviews = DB::table('sample_reviews')->where('product_id', $p->id)->get();

        return $this->data([...$this->catalog->card($p, $c), 'description' => $p->description, 'edition' => $p->edition,
            'gift_card' => $p->gift_face_amount_minor ? ['face_value' => $this->money->value($p->gift_face_amount_minor, $p->gift_face_currency)] : null,
            'subscription' => $p->subscription_duration_months ? ['duration_months' => $p->subscription_duration_months] : null,
            'gallery' => DB::table('product_images')->where('product_id', $p->id)->orderBy('position')->get()->map(fn ($i) => ['url' => $i->asset_path, 'alt' => $i->alt_text, 'position' => $i->position]),
            'reviews' => ['is_sample' => true, 'average_rating' => $reviews->avg('rating'), 'count' => $reviews->count(), 'items' => $reviews],
            'related_products' => $related->filter(fn ($id) => isset($cards[$id]) && $cards[$id]->starting_price !== null)->take(5)->map(fn ($id) => $this->catalog->card($cards[$id], $c))->values()]);
    }

    public function offers(Request $r, string $slug)
    {
        $p = $this->catalog->query()->where('p.slug', $slug)->first();
        abort_unless($p, 404);
        $offers = collect($this->catalog->offers($p->id, $this->currency($r)));

        return $this->data(['total_offers' => $offers->count(), 'featured_offer_id' => $offers->firstWhere('is_featured', true)['id'] ?? null, 'recommended' => $offers->whereNotNull('recommended_position')->sortBy('recommended_position')->values(), 'other' => $offers->whereNull('recommended_position')->values()]);
    }

    public function byIds(Request $r)
    {
        $r->validate(['ids' => 'required|array|max:10', 'ids.*' => 'uuid']);
        $c = $this->currency($r);
        $ids = array_unique($r->input('ids'));
        $rows = $this->catalog->query()->whereIn('p.id', $ids)->get()->keyBy('id');

        return $this->data(array_values(array_map(fn ($id) => $this->catalog->card($rows[$id], $c), array_filter($ids, fn ($id) => isset($rows[$id])))));
    }

    public function sections()
    {
        return $this->data(DB::table('content_blocks')->where('is_enabled', true)->orderBy('position')->get(['key', 'renderer_type', 'title', 'position'])->map(fn ($b) => [...(array) $b, 'content_url' => '/api/v1/homepage/sections/'.$b->key, 'requires_history' => $b->key === 'recently_viewed']));
    }

    public function section(Request $r, string $key)
    {
        $block = DB::table('content_blocks')->where('key', $key)->where('is_enabled', true)->first();
        abort_unless($block, 404);
        $c = $this->currency($r);
        $r->validate(['ids' => 'sometimes|array|max:10', 'ids.*' => 'uuid']);
        $services = ['hero' => Banner::class, 'shortcuts' => Navigation::class, 'featured' => FeaturedProducts::class, 'top_games' => TopGames::class, 'gaming_gift_cards' => GamingCards::class, 'subscriptions' => Subscriptions::class, 'recently_viewed' => RecentlyViewed::class, 'genres' => Genres::class];
        abort_unless(isset($services[$key]), 404, 'Section renderer unavailable.');

        return $this->data(app($services[$key])->content($block, $c, $r->input('ids', [])));
    }

    private function items(Request $r): array
    {
        return $r->validate(['items' => 'present|array|max:400', 'items.*.offer_id' => 'required|uuid|distinct', 'items.*.quantity' => 'required|integer|min:1|max:10000'])['items'];
    }

    public function preview(Request $r)
    {
        return $this->data($this->cart->render(null, $this->items($r), $this->currency($r)));
    }

    public function cart(Request $r)
    {
        return $this->data($this->cart->read($r->user()->id, $this->currency($r)));
    }

    public function setItem(Request $r, string $offer)
    {
        $d = $r->validate(['quantity' => 'required|integer|min:1|max:10000', 'expected_revision' => 'required|integer|min:0']);
        $this->cart->set($r->user()->id, $offer, (int) $d['quantity'], (string) $d['expected_revision']);

        return $this->cart($r);
    }

    public function deleteItem(Request $r, string $offer)
    {
        $r->validate(['expected_revision' => 'required|integer|min:0']);
        $this->cart->set($r->user()->id, $offer, 0, (string) $r->input('expected_revision'));

        return $this->cart($r);
    }

    public function merge(Request $r)
    {
        $r->validate(['transfer_id' => 'required|uuid']);
        $replayed = $this->cart->merge($r->user()->id, $r->input('transfer_id'), $this->items($r));

        return $this->data($this->cart->read($r->user()->id, $this->currency($r)), ['replayed' => $replayed]);
    }

    public function quote(Request $r)
    {
        $r->validate(['expected_revision' => 'required|integer|min:0', 'email' => 'prohibited', 'items' => 'prohibited']);

        return $this->data($this->checkout->quote($r->user()->id, $this->currency($r), (string) $r->input('expected_revision')));
    }

    public function purchase(Request $r)
    {
        $r->merge(['attempt_key' => $r->header('Idempotency-Key')]);
        $d = $r->validate(['quote_token' => 'required|string|max:100000', 'payment_method' => ['required', Rule::in(['card', 'paypal', 'digital_wallet'])], 'simulate_failure' => 'required|boolean', 'attempt_key' => 'required|uuid']);
        $result = $this->checkout->purchase($r->user()->id, $d['attempt_key'], $d['quote_token'], $d['payment_method'], (bool) $d['simulate_failure'], (int) $r->session()->get('auth_version'));
        if (($result['body']['data']['status'] ?? null) === 'succeeded') {
            $id = $result['body']['data']['order_id'];
            // Email delivery is deliberately outside the committed stock/order transaction.
            app(OrderDelivery::class)->send($id);
        }

        return response()->json($result['body'], $result['http'])->header('Idempotency-Replayed', $result['replayed'] ? 'true' : 'false');
    }

    public function email(Request $r, string $id)
    {
        app(GuestCheckout::class)->authorizedOrder($r, $id);

        return $this->data(app(OrderDelivery::class)->send($id));
    }

    public function claim(Request $r, string $id)
    {
        app(GuestCheckout::class)->claim($r, $id, $r->user()->id, $r->user()->email);

        return $this->order($r, $id);
    }

    public function orders(Request $r)
    {
        $r->validate(['page' => 'sometimes|integer|min:1']);
        $p = DB::table('orders')->where('user_id', $r->user()->id)->orderByDesc('purchased_at')->orderBy('id')->paginate(12);
        $items = collect($p->items())->map(function ($o) {
            $lines = DB::table('order_items')->where('order_id', $o->id)->get();

            return ['id' => $o->id, 'order_number' => $o->order_number, 'purchased_at' => $o->purchased_at, 'status' => $o->status, 'payment_method' => $o->payment_method, 'currency' => $o->currency, 'total' => $this->money->value($o->total_minor, $o->currency), 'item_count' => $lines->count(), 'unit_count' => $lines->sum('quantity'), 'products' => $lines->map(fn ($i) => json_decode($i->product_snapshot, true))];
        });

        return $this->data($items, ['page' => $p->currentPage(), 'total' => $p->total(), 'last_page' => $p->lastPage(), 'per_page' => 12]);
    }

    public function library(Request $r)
    {
        $d = $r->validate(['page' => 'sometimes|integer|min:1', 'q' => 'sometimes|nullable|string|max:120']);
        $query = DB::table('order_items as i')->join('orders as o', 'o.id', '=', 'i.order_id')->where('o.user_id', $r->user()->id);
        $term = mb_strtolower(trim($d['q'] ?? ''));
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->whereRaw("strpos(lower(i.product_snapshot->>'title'), ?) > 0", [$term])->orWhereRaw('strpos(lower(o.order_number), ?) > 0', [$term]);
                if (mb_strlen($term) >= 3) {
                    $q->orWhereRaw("word_similarity(?, lower(i.product_snapshot->>'title')) > 0.3", [$term]);
                }
            });
            // Exact and literal matches precede approximate title matches. Order IDs stay literal.
            $query->orderByRaw("CASE WHEN lower(i.product_snapshot->>'title') = ? OR lower(o.order_number) = ? THEN 4 WHEN strpos(lower(i.product_snapshot->>'title'), ?) = 1 THEN 3 WHEN strpos(lower(i.product_snapshot->>'title'), ?) > 0 OR strpos(lower(o.order_number), ?) > 0 THEN 2 ELSE 1 END DESC", [$term, $term, $term, $term, $term]);
            $query->orderByRaw("word_similarity(?, lower(i.product_snapshot->>'title')) DESC", [$term]);
        }
        $page = $query->orderByDesc('o.purchased_at')->orderBy('i.id')->select('i.*', 'o.order_number', 'o.purchased_at', 'o.currency')->paginate(12);
        $items = collect($page->items())->map(fn ($i) => ['id' => $i->id, 'order_id' => $i->order_id, 'order_number' => $i->order_number, 'purchased_at' => $i->purchased_at, 'product' => json_decode($i->product_snapshot, true), 'seller' => $i->seller_name_snapshot, 'quantity' => $i->quantity, 'total' => $this->money->value($i->line_total_minor, $i->currency)]);

        return $this->data($items, ['page' => $page->currentPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()])->header('Cache-Control', 'private, no-store');
    }

    public function order(Request $r, string $id)
    {
        $order = app(GuestCheckout::class)->authorizedOrder($r, $id);
        $items = DB::table('order_items')->where('order_id', $id)->get()->map(function ($i) use ($order) {
            return ['id' => $i->id, 'product' => json_decode($i->product_snapshot, true), 'seller' => $i->seller_name_snapshot, 'quantity' => $i->quantity, 'unit_price' => $this->money->value($i->charged_unit_minor, $order->currency), 'line_total' => $this->money->value($i->line_total_minor, $order->currency), 'keys' => DB::table('order_keys')->where('order_item_id', $i->id)->orderBy('unit_number')->get()->map(fn ($k) => ['unit_number' => $k->unit_number, 'value' => Crypt::decryptString($k->key_ciphertext), 'is_simulated' => true])];
        });

        return $this->data([...(array) $order, 'total' => $this->money->value($order->total_minor, $order->currency), 'items' => $items, 'is_guest_order' => ! $order->user_id, 'email_delivery' => app(OrderDelivery::class)->status($id)])->header('Cache-Control', 'private, no-store');
    }
}
