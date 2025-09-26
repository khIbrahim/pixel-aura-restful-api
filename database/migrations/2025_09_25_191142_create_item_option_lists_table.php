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
        Schema::create('item_option_lists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();

            $table->foreignId('option_list_id')
                ->constrained('option_lists')
                ->cascadeOnDelete();

            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('min_selections')->default(0);
            $table->unsignedInteger('max_selections')->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_option_lists');
    }
};
