<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->nullable(false)
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name', 255)->nullable(false);
            $table->string('type', 50)->nullable(false);
            $table->string('fingerprint_hash')->unique();
            $table->text('fingerprint')->nullable();
            $table->string('serial_number')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();

            $table->json('device_info')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('settings')->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->json('allowed_ip_ranges')->nullable();

            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->string('last_known_ip')->nullable();
            $table->integer('failed_auth_attempts')->default(0);
            $table->timestamp('last_failed_auth_at')->nullable();

            $table->string('firmware_version')->nullable();
            $table->string('app_version')->default('1.0.0')->nullable();
            $table->boolean('needs_update')->default(false);
            $table->timestamp('last_updated_at')->nullable();

            $table->integer('total_transactions')->default(0);
            $table->decimal('uptime_percentage', 5)->default(0.00);
            $table->integer('avg_response_time_ms')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'is_active']);
            $table->index(['fingerprint_hash']);
            $table->index(['last_seen_at']);
            $table->index(['is_blocked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
