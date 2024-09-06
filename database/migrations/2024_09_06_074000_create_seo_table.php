<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->string('title', 60)->nullable();
            $table->string('description', 160)->nullable();
            $table->string('image')->nullable();
            $table->string('author')->nullable();
            $table->json('robots')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo');
    }
};
