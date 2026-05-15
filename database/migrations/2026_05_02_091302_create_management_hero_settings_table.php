<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_hero_settings', function (Blueprint $table) {
            $table->id();
            $table->string('badge')->nullable();
            $table->string('title')->nullable();
            $table->string('title_highlight')->nullable();
            $table->text('description')->nullable();
            $table->string('primary_button_label')->nullable();
            $table->string('primary_button_url')->nullable();
            $table->string('secondary_button_label')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('tertiary_button_label')->nullable();
            $table->string('tertiary_button_url')->nullable();
            $table->string('image_one')->nullable();
            $table->string('image_one_alt')->nullable();
            $table->string('image_two')->nullable();
            $table->string('image_two_alt')->nullable();
            $table->string('image_three')->nullable();
            $table->string('image_three_alt')->nullable();
            $table->string('image_four')->nullable();
            $table->string('image_four_alt')->nullable();
            $table->string('floating_badge_top_icon', 30)->nullable();
            $table->string('floating_badge_top_title')->nullable();
            $table->string('floating_badge_top_subtitle')->nullable();
            $table->string('floating_badge_bottom_icon', 30)->nullable();
            $table->string('floating_badge_bottom_title')->nullable();
            $table->string('floating_badge_bottom_subtitle')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_hero_settings');
    }
};
