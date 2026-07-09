<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('store_print_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')
                ->unique()
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('customer_printer_name')->nullable();
            $table->string('receipt_printer_name')->nullable();
            $table->string('kitchen_printer_name')->nullable();

            $table->boolean('auto_print_customer_on_order_created')->default(true);
            $table->boolean('auto_print_receipt_on_order_created')->default(true);
            $table->boolean('auto_print_kitchen_on_preparing')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_print_settings');
    }
};
