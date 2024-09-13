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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->json('images')->nullable()->default(json_encode([]));
            $table->json('videos')->nullable()->default(json_encode([]));
            $table->boolean('published');
            $table->unsignedTinyInteger('visibility');
            $table->unsignedTinyInteger('type');
            $table->string('manufacturer', 50)->nullable();
            $table->json('specifications')->nullable()->default(json_encode([]));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
