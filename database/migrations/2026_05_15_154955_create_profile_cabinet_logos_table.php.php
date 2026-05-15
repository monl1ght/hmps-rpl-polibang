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
        Schema::create('profile_cabinet_logos', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('cabinet_name')->nullable();
            $table->string('period')->nullable();

            $table->string('image')->nullable();
            $table->string('alt_text')->nullable();

            $table->text('description')->nullable();
            $table->json('meta')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'profile_cabinet_logos_active_order_index');
            $table->index(['cabinet_name', 'period'], 'profile_cabinet_logos_cabinet_period_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_cabinet_logos');
    }
};
