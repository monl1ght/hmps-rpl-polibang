<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_division_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('position');
            // Ketua, Sekretaris, Bendahara, Koordinator, Anggota

            $table->string('category')->default('member');
            // core, coordinator, member

            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_members');
    }
};