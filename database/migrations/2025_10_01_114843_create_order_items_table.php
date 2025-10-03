<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();
            $table->foreignId('variant_id')
                ->nullable()
                ->constrained('item_variants')
                ->nullOnDelete();

            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->text('item_image_url')->nullable();
            $table->string('item_sku')->nullable();

            $table->string('variant_name')->nullable();

            $table->json('selected_options')->nullable();
            $table->json('ingredient_modifications')->nullable();

            $table->integer('base_price_cents'); // Item base price
            $table->integer('options_price_cents')->default(0); // Options total
            $table->integer('ingredients_price_cents')->default(0); // Ingredients mods total
            $table->integer('item_total_cents'); // base + options + ingredients

            $table->integer('quantity')->default(1);
            $table->integer('final_total_cents'); // item_total * quantity

            $table->text('special_instructions')->nullable();

            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['order_id', 'item_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
