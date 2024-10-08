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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('tax', 12)->default(0);
            $table->decimal('shipping_fee', 12)->default(0);
            $table->decimal('handling_fee', 12)->default(0);
            $table->smallInteger('weight');
            $table->decimal('total', 12)->default(0);
            $table->unsignedTinyInteger('status');
            $table->string('note')->nullable();
            $table->unsignedTinyInteger('shipping_method');
            $table->unsignedTinyInteger('payment_method');
            $table->string('shipping_code', 50)->nullable();
            $table->string('payment_checkout_url')->nullable();
            $table->foreignId('address_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
