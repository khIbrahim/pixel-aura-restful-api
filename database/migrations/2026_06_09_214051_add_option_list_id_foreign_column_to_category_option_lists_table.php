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
        Schema::table('category_option_lists', function (Blueprint $table) {
            $table->foreignId('option_list_id')
                ->after('store_id')
                ->constrained('option_lists')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_option_lists', function (Blueprint $table) {
            $table->dropForeign('category_option_lists_option_list_id_foreign');
        });
    }
};
