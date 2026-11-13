<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('content_blocks')->insertOrIgnore(['id' => (string) Str::uuid(), 'key' => 'genres', 'renderer_type' => 'genres', 'title' => 'Categories', 'position' => 7, 'is_enabled' => true, 'configuration' => json_encode(['cta_label' => 'Show all games', 'cta_url' => '/catalog?type=game'])]);
    }

    public function down(): void
    {
        DB::table('content_blocks')->where('key', 'genres')->delete();
    }
};
