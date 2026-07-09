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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->uuid('event_id')
                ->nullable()
                ->unique();

            $table->string('type');
            $table->string('title');
            $table->string('message');
            $table->string('severity');
            $table->string('subject_type')
                ->nullable();
            $table->string('subject_id')
                ->nullable();
            $table->string('subject_number')
                ->nullable();
            $table->string('action_url')
                ->nullable();

            $table->json('data')
                ->nullable();
            $table->timestampTz('read_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
