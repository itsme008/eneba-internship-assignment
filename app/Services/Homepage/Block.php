<?php

namespace App\Services\Homepage;

use App\Services\Catalog;
use Illuminate\Support\Facades\DB;

abstract class Block
{
    public function __construct(protected Catalog $catalog) {}

    protected function productIds(object $block, array $history): array
    {
        return DB::table('content_block_products')->where('block_id', $block->id)->orderBy('position')->pluck('product_id')->all();
    }

    public function content(object $block, string $currency, array $history = []): array
    {
        $ids = array_values(array_unique($this->productIds($block, $history)));
        $rows = $this->catalog->query()->whereIn('p.id', $ids)->get()->keyBy('id');

        return ['key' => $block->key, 'renderer_type' => $block->renderer_type, 'title' => $block->title, 'configuration' => json_decode($block->configuration, true), 'items' => array_values(array_map(fn ($id) => $this->catalog->card($rows[$id], $currency), array_filter($ids, fn ($id) => isset($rows[$id]))))];
    }
}
