<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->foreignId('device_id')
                ->nullable()
                ->index()
                ->constrained('devices');

            $table->foreignId('store_id')
                ->nullable()
                ->index()
                ->constrained('stores');

            $table->uuid('fingerprint')
                ->nullable()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'store_id', 'fingerprint']);
        });
    }
};
