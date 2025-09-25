<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_levels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('variant_id');
            $table->foreign('variant_id')->references('id')->on('item_variants')->onDelete('cascade');

            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

            $table->integer('quantity')->default(0);

            $table->integer('reorder_level')->nullable();
            $table->integer('min_stock')->nullable();
            $table->integer('max_stock')->nullable();

            $table->timestamps();

            $table->unique(['variant_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_levels');
    }
};
