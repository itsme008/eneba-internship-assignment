<?php

namespace App\Services\Homepage;

use Illuminate\Support\Facades\DB;

final class Navigation extends Block
{
    public function content(object $block, string $currency, array $history = []): array
    {
        $content = parent::content($block, $currency, $history);
        $links = array_map(fn ($link) => ['title' => $link['title'], 'icon' => match ($link['type']) {
            'gift_card' => 'gift', 'subscription' => 'layers', default => 'gamepad'
        }, 'url' => '/catalog?type='.rawurlencode($link['type'])], $content['configuration']['links'] ?? []);
        foreach (DB::table('platforms')->orderBy('name')->get() as $platform) {
            $links[] = ['title' => $platform->name, 'icon' => 'gamepad', 'url' => '/catalog?platform='.rawurlencode($platform->code)];
        }
        $content['configuration']['links'] = $links;

        return $content;
    }
}
