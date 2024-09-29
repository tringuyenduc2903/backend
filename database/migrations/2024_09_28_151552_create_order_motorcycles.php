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
        Schema::create('order_motorcycles', function (Blueprint $table) {
            $table->id();
            $table->decimal('tax', 12)->default(0);
            $table->decimal('handling_fee', 12)->default(0);
            $table->decimal('motorcycle_registration_support_fee', 12)->default(0);
            $table->decimal('registration_fee', 12)->default(0);
            $table->decimal('license_plate_registration_fee', 12)->default(0);
            $table->decimal('total', 12)->default(0);
            $table->unsignedTinyInteger('status');
            $table->string('note')->nullable();
            $table->boolean('motorcycle_registration_support');
            $table->unsignedTinyInteger('registration_option')->nullable();
            $table->unsignedTinyInteger('license_plate_registration_option')->nullable();
            $table->unsignedTinyInteger('payment_method');
            $table->string('payment_checkout_url')->nullable();
            $table->decimal('price', 12)->default(0);
            $table->unsignedSmallInteger('value_added_tax');
            $table->unsignedSmallInteger('amount');
            $table->foreignId('option_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('motor_cycle_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
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
        Schema::dropIfExists('order_motorcycles');
    }
};
