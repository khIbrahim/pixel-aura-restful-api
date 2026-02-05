<?php

use App\Enum\V1\Discount\DiscountStatus;
use App\Enum\V1\Discount\DiscountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();

            $table->enum('type', array_map(fn($case) => $case->value, DiscountType::cases()));
            $table->enum('status', array_map(fn($case) => $case->value, DiscountStatus::cases()))
                ->default(DiscountStatus::Draft->value);

            $table->integer('value')
                ->default(0);

            $table->json('config')
                ->comment("Selon le type de discount, configurer les paramètres spécifiques");

            $table->json('conditions')
                ->nullable()
                ->comment("Exemple : " . json_encode([
                    'min_order_amount_cents' => 2000,
                    'min_items_quantity'     => 3,
                    'min_customer_orders'    => 1,
                    'max_order_amount_cents' => 10000,
                ]));

            $table->json('target')
                ->nullable()
                ->comment("Exemple : " . json_encode([
                        'applicable_items'      => [1, 2, 3],
                        'applicable_categories' => [1, 2, 3],
                        'excluded_items'        => [4, 5, 6],
                        'excluded_categories'   => [4, 5, 6],
                    ]));

            $table->timestamp('valid_from')
                ->nullable();
            $table->timestamp('valid_until')
                ->nullable();

            $table->integer('max_uses')
                ->nullable();
            $table->integer('max_uses_per_customer')
                ->nullable();
            $table->integer('max_discount_cents')
                ->nullable();
            $table->integer('current_uses')
                ->default(0);

            $table->boolean('is_combinable')
                ->default(false);
            $table->json('combinable_with')
                ->nullable()
                ->comment("IDs des autres discounts combinables");
            $table->integer('priority')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);
            $table->json('metadata')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('store_members')
                ->nullOnDelete();

            $table->timestamp('applied_at')
                ->nullable();
            $table->foreignId('applied_by')
                ->nullable()
                ->constrained('store_members')
                ->nullOnDelete();

            $table->index(['store_id', 'code']);
            $table->index(['valid_from', 'valid_until']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
