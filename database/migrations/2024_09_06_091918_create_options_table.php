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
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->decimal('price', 14)->default(0);
            $table->unsignedTinyInteger('value_added_tax');
            $table->json('images')->nullable()->default(json_encode([]));
            $table->string('color', 50)->nullable();
            $table->string('version', 50)->nullable();
            $table->string('volume', 50)->nullable();
            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('status');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedInteger('length')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('specifications')->nullable()->default(json_encode([]));
            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
