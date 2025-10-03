<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->onDelete('cascade');
            $table->foreignId('option_list_id')
                ->constrained('option_lists')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price_cents');
            $table->boolean('is_active')->default(true);
            $table->integer('preparation_time_minutes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
