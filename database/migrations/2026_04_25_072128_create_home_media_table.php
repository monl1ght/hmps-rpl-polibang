<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_media', function (Blueprint $table) {
            $table->id();

            $table->string('group');
            // hero, gallery

            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('category')->nullable();

            $table->string('image')->nullable();
            $table->string('alt_text')->nullable();

            $table->string('layout')->default('normal');
            // normal, large, wide

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_media');
    }
};