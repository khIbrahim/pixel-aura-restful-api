<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();

            $table->string('mode', 20); // percentage | fixed
            $table->decimal('rate', 8, 4)->default(0); // Pourcentage (ex: 20.0000 = 20%)
            $table->decimal('amount', 12, 4)->nullable(); // Montant fixe éventuel

            $table->boolean('inclusive')->default(false);
            $table->boolean('compound')->default(false);
            $table->unsignedSmallInteger('priority')->default(100);

            $table->char('country_code', 2)->nullable();
            $table->string('region_code', 10)->nullable();

            $table->string('applies_to', 30)->default('items'); // items | categories | shipping | orders | service_fees
            $table->boolean('active')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedTinyInteger('rounding_strategy')->default(0); // 0=none 1=line 2=total 3=unit
            $table->unsignedTinyInteger('rounding_precision')->default(2);

            $table->string('external_id', 100)->nullable();
            $table->json('metadata')->nullable();

            $table->softDeletes();

            $table->index(['mode']);
            $table->index(['applies_to']);
            $table->index(['active', 'starts_at', 'ends_at']);
            $table->index(['country_code', 'region_code']);
            $table->index(['priority', 'compound']);
            $table->index(['external_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
