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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->string('name', 120)->nullable(false);
            $table->string('sku', 140)->nullable(false);

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('phone', 40)->nullable();
            $table->string('email', 120)->nullable();

            $table->string('address', 160)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            $table->string('currency', 3)->default('DZD');
            $table->string('language', 8)->default('fr');
            $table->string('timezone', 64)->default('Africa/Algiers');
            $table->boolean('tax_inclusive')->default(true);
            $table->decimal('default_vat_rate', 5)->default(0.00);

            $table->json('receipt_settings')->nullable();
            $table->json('settings')->nullable();
            $table->integer('menu_version')
                ->unsigned()
                ->default(1);

//            $table->foreignId('owner_member_id')
//                ->nullable()
//                ->constrained('store_members')
//                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
