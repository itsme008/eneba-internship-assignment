<?php

use App\Services\OrderDelivery;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:send-emails {--limit=10}', function () {
    $limit = max(1, min(100, (int) $this->option('limit')));
    $ids = DB::table('order_email_deliveries')->where('status', '!=', 'sent')->where('attempts', '<', 10)->where(fn ($q) => $q->whereNull('lease_until')->orWhere('lease_until', '<=', now()))->orderBy('created_at')->limit($limit)->pluck('order_id');
    $counts = [];
    foreach ($ids as $id) {
        $result = app(OrderDelivery::class)->send($id);
        $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;
    }
    $this->info(json_encode(['processed' => $ids->count(), 'statuses' => $counts]));
})->purpose('Retry pending key emails without modifying purchases; sends to their recorded recipients');
