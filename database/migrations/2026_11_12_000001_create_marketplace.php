<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        foreach (['platforms', 'regions', 'genres'] as $name) {
            Schema::create($name, function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->string('code')->unique();
                $t->string('name');
            });
        }
        Schema::create('products', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('slug')->unique();
            $t->string('title');
            $t->string('product_type');
            $t->foreignUuid('platform_id')->constrained();
            $t->foreignUuid('region_id')->constrained();
            $t->text('description');
            $t->string('edition')->nullable();
            $t->bigInteger('gift_face_amount_minor')->nullable();
            $t->char('gift_face_currency', 3)->nullable();
            $t->smallInteger('subscription_duration_months')->nullable();
            $t->integer('popularity_score');
            $t->decimal('demo_cashback_percent', 5, 2)->default(5);
            $t->boolean('is_active')->default(true);
            $t->timestampTz('catalog_added_at');
            $t->timestampsTz();
        });
        DB::statement("ALTER TABLE products ADD CONSTRAINT product_type_attributes CHECK ((product_type = 'game' AND gift_face_amount_minor IS NULL AND gift_face_currency IS NULL AND subscription_duration_months IS NULL) OR (product_type = 'gift_card' AND gift_face_amount_minor IS NOT NULL AND gift_face_amount_minor > 0 AND gift_face_currency IS NOT NULL AND subscription_duration_months IS NULL) OR (product_type = 'subscription' AND subscription_duration_months IS NOT NULL AND subscription_duration_months > 0 AND gift_face_amount_minor IS NULL AND gift_face_currency IS NULL))");
        DB::statement('CREATE INDEX product_title_trgm ON products USING gin (lower(title) gin_trgm_ops)');
        Schema::create('product_genres', function (Blueprint $t) {
            $t->foreignUuid('product_id')->constrained();
            $t->foreignUuid('genre_id')->constrained();
            $t->primary(['product_id', 'genre_id']);
        });
        Schema::create('product_images', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('product_id')->constrained();
            $t->text('asset_path');
            $t->string('alt_text');
            $t->integer('position');
            $t->unique(['product_id', 'position']);
        });
        Schema::create('sellers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('slug')->unique();
            $t->string('display_name');
            $t->decimal('rating_score', 4, 2);
            $t->boolean('is_active')->default(true);
        });
        Schema::create('offers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('product_id')->constrained();
            $t->foreignUuid('seller_id')->constrained();
            $t->bigInteger('price_eur_minor');
            $t->bigInteger('compare_at_eur_minor')->nullable();
            $t->integer('stock_quantity');
            $t->boolean('is_active')->default(true);
            $t->smallInteger('recommended_position')->nullable();
            $t->boolean('is_featured')->default(false);
            $t->timestampsTz();
            $t->unique(['product_id', 'seller_id']);
            $t->index(['product_id', 'is_active', 'price_eur_minor']);
        });
        DB::statement('ALTER TABLE offers ADD CONSTRAINT offer_stock_price CHECK (stock_quantity >= 0 AND price_eur_minor > 0)');
        DB::statement('CREATE UNIQUE INDEX one_featured_offer ON offers (product_id) WHERE is_featured');
        DB::statement('CREATE UNIQUE INDEX recommended_offer_rank ON offers (product_id, recommended_position) WHERE recommended_position IS NOT NULL');
        Schema::create('sample_reviews', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('product_id')->constrained();
            $t->string('display_author');
            $t->smallInteger('rating');
            $t->text('body');
            $t->timestampTz('reviewed_at');
            $t->boolean('is_sample')->default(true);
        });
        Schema::create('content_blocks', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('key')->unique();
            $t->string('renderer_type');
            $t->string('title');
            $t->integer('position');
            $t->boolean('is_enabled')->default(true);
            $t->jsonb('configuration');
        });
        Schema::create('content_block_products', function (Blueprint $t) {
            $t->foreignUuid('block_id')->constrained('content_blocks');
            $t->foreignUuid('product_id')->constrained();
            $t->integer('position');
            $t->primary(['block_id', 'product_id']);
            $t->unique(['block_id', 'position']);
        });
        Schema::create('related_products', function (Blueprint $t) {
            $t->foreignUuid('product_id')->constrained();
            $t->foreignUuid('related_product_id')->constrained('products');
            $t->integer('position');
            $t->primary(['product_id', 'related_product_id']);
        });
        DB::statement('ALTER TABLE related_products ADD CONSTRAINT distinct_related CHECK (product_id <> related_product_id)');
        Schema::create('fx_rate_sets', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('version')->unique();
            $t->char('base_currency', 3)->default('EUR');
            $t->timestampTz('effective_at');
        });
        Schema::create('fx_rates', function (Blueprint $t) {
            $t->foreignUuid('rate_set_id')->constrained('fx_rate_sets');
            $t->char('currency', 3);
            $t->decimal('rate', 18, 8);
            $t->primary(['rate_set_id', 'currency']);
        });
        Schema::create('carts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('user_id')->unique()->constrained();
            $t->bigInteger('revision')->default(0);
            $t->timestampsTz();
        });
        Schema::create('cart_items', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('cart_id')->constrained();
            $t->foreignUuid('offer_id')->constrained();
            $t->integer('quantity');
            $t->unique(['cart_id', 'offer_id']);
        });
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT positive_cart_quantity CHECK (quantity > 0 AND quantity <= 10000)');
        Schema::create('cart_merges', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('user_id')->constrained();
            $t->foreignUuid('cart_id')->constrained();
            $t->uuid('transfer_id');
            $t->char('payload_hash', 64);
            $t->bigInteger('applied_revision');
            $t->timestampTz('merged_at');
            $t->unique(['user_id', 'transfer_id']);
        });
        Schema::create('checkout_attempts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('user_id')->constrained();
            $t->foreignUuid('cart_id')->constrained();
            $t->uuid('idempotency_key');
            $t->char('request_hash', 64);
            $t->string('status');
            $t->string('payment_method');
            $t->boolean('simulate_failure');
            $t->integer('result_http_status')->nullable();
            $t->jsonb('result_payload')->nullable();
            $t->timestampTz('completed_at')->nullable();
            $t->timestampsTz();
            $t->unique(['user_id', 'idempotency_key']);
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('order_number')->unique();
            $t->foreignUuid('checkout_attempt_id')->unique()->constrained();
            $t->foreignUuid('user_id')->constrained();
            $t->string('buyer_email_snapshot');
            $t->string('status')->default('completed');
            $t->string('payment_method');
            $t->char('currency', 3);
            $t->foreignUuid('rate_set_id')->constrained('fx_rate_sets');
            $t->decimal('exchange_rate_snapshot', 18, 8);
            $t->bigInteger('subtotal_minor');
            $t->bigInteger('total_minor');
            $t->bigInteger('base_total_eur_minor');
            $t->timestampTz('purchased_at');
            $t->index(['user_id', 'purchased_at']);
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->constrained();
            $t->foreignUuid('offer_id')->constrained();
            $t->foreignUuid('product_id')->constrained();
            $t->foreignUuid('seller_id')->constrained();
            $t->integer('quantity');
            $t->jsonb('product_snapshot');
            $t->string('seller_name_snapshot');
            $t->bigInteger('base_unit_eur_minor');
            $t->bigInteger('charged_unit_minor');
            $t->bigInteger('line_total_minor');
            $t->unique(['order_id', 'offer_id']);
        });
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT valid_order_amount CHECK (quantity > 0 AND charged_unit_minor >= 0 AND line_total_minor = quantity * charged_unit_minor)');
        Schema::create('order_keys', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_item_id')->constrained();
            $t->integer('unit_number');
            $t->text('key_ciphertext');
            $t->char('key_digest', 64)->unique();
            $t->timestampTz('created_at');
            $t->unique(['order_item_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        foreach (['order_keys', 'order_items', 'orders', 'checkout_attempts', 'cart_merges', 'cart_items', 'carts', 'fx_rates', 'fx_rate_sets', 'related_products', 'content_block_products', 'content_blocks', 'sample_reviews', 'offers', 'sellers', 'product_images', 'product_genres', 'products', 'genres', 'regions', 'platforms'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
