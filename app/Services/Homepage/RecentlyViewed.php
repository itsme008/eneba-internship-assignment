<?php

namespace App\Services\Homepage;

final class RecentlyViewed extends Block
{
    protected function productIds(object $block, array $history): array
    {
        return array_slice($history, 0, 10);
    }
}
