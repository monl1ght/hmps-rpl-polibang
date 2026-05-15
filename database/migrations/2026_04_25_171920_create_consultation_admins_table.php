<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_admins', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('name');
            $table->string('day');
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('20:00:00');
            $table->string('phone_display')->nullable();
            $table->string('phone_wa');
            $table->string('role')->nullable();
            $table->string('emoji')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['day', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_admins');
    }
};