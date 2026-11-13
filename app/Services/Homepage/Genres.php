<?php

namespace App\Services\Homepage;

use Illuminate\Support\Facades\DB;

final class Genres extends Block
{
    public function content(object $block, string $currency, array $history = []): array
    {
        $content = parent::content($block, $currency, $history);
        $content['items'] = DB::table('genres as g')->leftJoin('product_genres as pg', 'pg.genre_id', '=', 'g.id')->leftJoin('products as p', function ($join) {
            $join->on('p.id', '=', 'pg.product_id')->where('p.is_active', true)->where('p.product_type', 'game');
        })->groupBy('g.id', 'g.code', 'g.name')->orderBy('g.name')->selectRaw('g.code, g.name, count(p.id) as product_count')->get()->map(fn ($g) => ['code' => $g->code, 'name' => $g->name, 'count' => (int) $g->product_count, 'url' => '/catalog?type=game&genre='.rawurlencode($g->code)])->all();

        return $content;
    }
}
