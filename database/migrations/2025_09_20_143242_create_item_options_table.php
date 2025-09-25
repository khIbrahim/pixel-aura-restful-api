<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('options')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('price_cents')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('name_override')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'option_id'], 'item_option_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_options');
    }
};
