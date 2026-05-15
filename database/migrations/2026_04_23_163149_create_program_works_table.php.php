<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_work_category_id')
                ->constrained('program_work_categories')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable()->unique();

            $table->string('schedule_text')->nullable();
            $table->string('division_name')->nullable();
            $table->string('person_in_charge')->nullable();
            $table->string('target')->nullable();

            $table->unsignedBigInteger('budget')->default(0);
            $table->string('image')->nullable();

            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();

            $table->json('goals')->nullable();
            $table->json('benefits')->nullable();
            $table->json('funding_sources')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_works');
    }
};