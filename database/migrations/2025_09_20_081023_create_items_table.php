<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('sku');
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();

            $table->foreignId('tax_id')
                ->nullable()
                ->constrained('taxes')
                ->nullOnDelete();

            $table->char('currency', 3)->default('EUR');
            $table->unsignedInteger('base_price_cents')->default(0);
            $table->unsignedInteger('current_cost_cents')->default(0);

            $table->boolean('is_active')->default(true);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('loyalty_eligible')->default(true);
            $table->unsignedTinyInteger('age_restriction')->nullable();

            $table->integer('reorder_level')->nullable();

            $table->unsignedInteger('weight_grams')->nullable();

            $table->json("tags")->nullable();
            $table->json('metadata')->nullable();
            $table->integer('preparation_time_minutes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('store_members')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('store_members')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'sku']);
            $table->unique(['store_id', 'barcode']);
            $table->index(['store_id', 'category_id']);
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'track_inventory']);
            $table->fullText('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
