<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('documentation_id')
                ->constrained('documentations')
                ->cascadeOnDelete();

            $table->string('image');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['documentation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_images');
    }
};