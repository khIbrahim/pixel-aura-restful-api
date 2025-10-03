<?php

use App\Enum\V1\Order\OrderChannel;
use App\Enum\V1\Order\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->enum('channel', array_map(fn($case) => $case->value, OrderChannel::cases()));
            /** @see \App\Enum\V1\Order\OrderServiceType */
            $table->string('service_type', 20)->index();

            $table->json('delivery')
                ->nullable()
                ->comment("Pour le service de livraison");
            $table->json('dine_in')
                ->nullable()
                ->comment("Pour le service en salle");
            $table->json('pickup')
                ->nullable()
                ->comment("Pour le service à emporter");

            $table->foreignId('device_id')
                ->nullable()
                ->constrained('devices')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('store_members')
                ->cascadeOnDelete();

            $table->integer('subtotal_cents')
                ->default(0);
            $table->integer('tax_cents')
                ->default(0);
            $table->integer('discount_cents')
                ->default(0);
            $table->integer('total_cents')
                ->default(0);
            $table->string('currency', 3)
                ->default('DZD');

            $table->enum('status', array_map(fn($case) => $case->value, OrderStatus::cases()));
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->json('metadata')->nullable();
            $table->text('special_instructions')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
