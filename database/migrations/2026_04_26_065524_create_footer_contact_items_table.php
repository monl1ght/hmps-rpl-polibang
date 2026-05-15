<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_contact_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('value')->nullable();
            $table->string('helper')->nullable();
            $table->string('href')->nullable();
            $table->string('type')->default('info');
            $table->text('icon_path')->nullable();
            $table->string('target')->default('_self');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_contact_items');
    }
};
