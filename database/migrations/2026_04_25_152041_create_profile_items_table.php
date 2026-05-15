<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_items', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('label')->nullable();
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_items');
    }
};