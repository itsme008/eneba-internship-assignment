<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Catalog
{
    public function __construct(public Money $money) {}

    public function query()
    {
        $prices = DB::table('offers as o')->join('sellers as s', 's.id', '=', 'o.seller_id')
            ->where('o.is_active', true)->where('s.is_active', true)->where('o.stock_quantity', '>', 0)
            ->groupBy('o.product_id')->selectRaw('o.product_id, MIN(o.price_eur_minor) as starting_price');

        return DB::table('products as p')->join('platforms as pl', 'pl.id', '=', 'p.platform_id')
            ->join('regions as r', 'r.id', '=', 'p.region_id')->leftJoinSub($prices, 'prices', 'prices.product_id', '=', 'p.id')
            ->leftJoin('product_images as i', fn ($j) => $j->on('i.product_id', '=', 'p.id')->where('i.position', 0))
            ->where('p.is_active', true)->select('p.*', 'pl.code as platform_code', 'pl.name as platform_name',
                'r.code as region_code', 'r.name as region_name', 'prices.starting_price', 'i.asset_path');
    }

    public function card(object $p, string $currency): array
    {
        $price = $p->starting_price === null ? null : $this->money->convert($p->starting_price, $currency);

        return ['id' => $p->id, 'slug' => $p->slug, 'title' => $p->title, 'type' => $p->product_type,
            'image' => ['url' => $p->asset_path ?: '/images/fallback.svg', 'alt' => $p->title],
            'platform' => ['code' => $p->platform_code, 'name' => $p->platform_name], 'region' => ['code' => $p->region_code, 'name' => $p->region_name],
            'starting_price' => $price === null ? null : $this->money->value($price, $currency),
            'availability' => $price === null ? 'sold_out' : 'available', 'catalog_added_at' => $p->catalog_added_at,
            'demo_cashback' => ['percent' => (string) $p->demo_cashback_percent, 'is_mock' => true]];
    }

    public function find(string $id, string $currency): array
    {
        $p = $this->query()->where('p.id', $id)->first();
        abort_unless($p, 404, 'Product not found.');

        return $this->card($p, $currency);
    }

    public function offers(string $productId, string $currency): array
    {
        return DB::table('offers as o')->join('sellers as s', 's.id', '=', 'o.seller_id')->where('o.product_id', $productId)
            ->where('o.is_active', true)->where('s.is_active', true)->orderBy('o.price_eur_minor')->orderBy('o.id')
            ->get(['o.*', 's.display_name', 's.rating_score'])->map(fn ($o) => [
                'id' => $o->id, 'product_id' => $o->product_id, 'seller' => ['id' => $o->seller_id, 'name' => $o->display_name, 'rating' => ['score' => (float) $o->rating_score, 'scale' => 10, 'is_mock' => true]],
                'unit_price' => $this->money->value($this->money->convert($o->price_eur_minor, $currency), $currency),
                'compare_at_price' => $o->compare_at_eur_minor ? $this->money->value($this->money->convert($o->compare_at_eur_minor, $currency), $currency) : null,
                'stock_quantity' => $o->stock_quantity, 'is_purchasable' => $o->stock_quantity > 0, 'is_featured' => $o->is_featured, 'recommended_position' => $o->recommended_position,
            ])->all();
    }
}
