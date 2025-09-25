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
        Schema::table('catalogs', function (Blueprint $table) {
            // Ajout des nouveaux champs après store_id
            $table->string('name')->after('store_id');
            $table->renameColumn('version', 'version_int');
            $table->timestamp('published_at')->nullable()->after('data');
            $table->string('idempotency_key')->nullable()->after('published_at');
            $table->boolean('is_draft')->default(true)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->renameColumn('version_int', 'version');
            $table->dropColumn(['name', 'is_draft', 'published_at', 'idempotency_key']);
        });
    }
};
