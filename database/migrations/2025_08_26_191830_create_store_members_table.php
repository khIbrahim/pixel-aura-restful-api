<?php

use App\Enum\V1\StoreMemberRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name', 120);

            $table->string('role', 20)
                ->default(StoreMemberRole::Cashier->value)
                ->nullable(false);

            $table->unsignedMediumInteger('code_number')->nullable();

            $table->string('pin_hash', 100)->charset('ascii')->collation('ascii_general_ci');
            $table->timestamp('pin_last_changed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('failed_attempts')->default(0);
            $table->dateTime('locked_until')->nullable();

            $table->json('permissions')->nullable();

            $table->boolean('is_active')->default(true);

            $table->json('meta')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Unicité par store+role+code
            $table->unique(
                ['store_id', 'role', 'code_number'],
                'store_members_store_id_role_code_number_unique'
            );

            // pour les filtres
            $table->index(['store_id', 'role', 'is_active'], 'store_members_store_role_active_idx');
            $table->index(['store_id', 'code_number'], 'store_members_store_code_idx');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_members');
    }
};
