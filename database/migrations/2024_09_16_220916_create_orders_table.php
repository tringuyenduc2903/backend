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
            $table->json('other_fees')->default(json_encode([]));
            $table->decimal('total', 12)->default(0);
            $table->tinyInteger('status');
            $table->string('note', 255)->nullable();
            $table->unsignedTinyInteger('shipping_type');
            $table->unsignedTinyInteger('transaction_type');
            $table->json('other_fields')->default(json_encode([]));
            $table->foreignId('address_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('identification_id')
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
