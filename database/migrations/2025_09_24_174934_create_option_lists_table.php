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
        Schema::create('option_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();
            $table->foreignId('option_list_id')
                ->constrained('option_lists')
                ->cascadeOnDelete()
                ->nullable();

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('sku', 100)->unique();
            $table->integer('min_selections')->default(0);
            $table->integer('max_selections')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_lists');
    }
};
