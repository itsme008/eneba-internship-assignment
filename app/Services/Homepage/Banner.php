<?php

namespace App\Services\Homepage;

final class Banner extends Block
{
    public function content(object $block, string $currency, array $history = []): array
    {
        $content = parent::content($block, $currency, $history);
        $content['configuration']['promotions'] = [
            $content['configuration'],
            ['image' => '/images/games/1091500-scene-1.jpg', 'heading_lines' => ['A new world'], 'heading_accent' => 'awaits you.', 'subtitle' => 'Explore our curated collection of games and compare seller offers.', 'cta_url' => '/catalog?type=game', 'cta_label' => 'Discover games'],
            ['image' => '/images/games/1174180-scene-1.jpg', 'heading_lines' => ['Big adventures.'], 'heading_accent' => 'Small prices.', 'subtitle' => 'Discover lower-priced games with sample offers from our demo sellers.', 'cta_url' => '/catalog?type=game&price_max_minor=1000', 'cta_label' => 'Browse cheap games'],
        ];

        return $content;
    }
}
