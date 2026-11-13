<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_checkouts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('email', 254);
            $t->timestampsTz();
        });
        foreach (['carts', 'checkout_attempts', 'orders'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('user_id')->nullable()->change();
                $t->foreignUuid('guest_checkout_id')->nullable()->constrained('guest_checkouts');
            });
        }
        Schema::table('carts', fn (Blueprint $t) => $t->unique('guest_checkout_id'));
        Schema::table('checkout_attempts', fn (Blueprint $t) => $t->unique(['guest_checkout_id', 'idempotency_key'], 'guest_attempt_identity'));
        foreach (['carts', 'checkout_attempts', 'orders'] as $table) {
            DB::statement("ALTER TABLE $table ADD CONSTRAINT {$table}_one_owner CHECK ((user_id IS NOT NULL)::int + (guest_checkout_id IS NOT NULL)::int = 1)");
        }
        Schema::create('order_email_deliveries', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('order_id')->unique()->constrained();
            $t->string('status')->default('pending');
            $t->unsignedInteger('attempts')->default(0);
            $t->uuid('lease_token')->nullable();
            $t->timestampTz('lease_until')->nullable();
            $t->timestampTz('last_attempt_at')->nullable();
            $t->timestampTz('sent_at')->nullable();
            $t->string('failure_code')->nullable();
            $t->timestampsTz();
        });
    }

    public function down(): void
    {
        if (DB::table('guest_checkouts')->exists()) {
            throw new RuntimeException('Guest checkout data exists. Use a reviewed forward migration; rollback would lose purchase ownership/replay history.');
        }
        Schema::dropIfExists('order_email_deliveries');
        foreach (['orders', 'checkout_attempts', 'carts'] as $table) {
            DB::statement("ALTER TABLE $table DROP CONSTRAINT {$table}_one_owner");
            Schema::table($table, fn (Blueprint $t) => $t->dropConstrainedForeignId('guest_checkout_id'));
        }
        // Guest rows cannot be safely converted into registered ownership on rollback.
        Schema::dropIfExists('guest_checkouts');
    }
};
