<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentations', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('category')->default('kegiatan');
            // kegiatan, proker, lainnya

            $table->unsignedSmallInteger('year');
            $table->date('event_date')->nullable();
            $table->string('location')->nullable();

            $table->string('cover')->nullable();
            $table->longText('description')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_published', 'is_featured', 'sort_order']);
            $table->index(['category', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};