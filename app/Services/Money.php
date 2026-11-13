<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Money
{
    private array $rates = [];

    public function rate(string $currency): object
    {
        abort_unless(in_array($currency, ['EUR', 'USD', 'GBP'], true), 422, 'Unsupported currency.');

        return $this->rates[$currency] ??= DB::table('fx_rates as r')->join('fx_rate_sets as s', 's.id', '=', 'r.rate_set_id')
            ->where('r.currency', $currency)->orderByDesc('s.effective_at')->first(['r.*', 's.version']);
    }

    public function convert(int|string $cents, string $currency): int
    {
        return (int) bcdiv(bcadd(bcmul((string) $cents, (string) $this->rate($currency)->rate, 8), '0.5', 8), '1', 0);
    }

    public function value(int|string $minor, string $currency): array
    {
        return ['currency' => $currency, 'minor' => (string) $minor];
    }
}
