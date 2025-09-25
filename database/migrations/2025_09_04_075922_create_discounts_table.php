<?php

use App\Enum\PricedValType;
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
            $table->text('description')->nullable();
            $table->enum('type', array_map(fn($t) => $t->value, PricedValType::cases()));
            $table->decimal('value'); // pourcentage ou euros
            $table->string('applies_to')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
