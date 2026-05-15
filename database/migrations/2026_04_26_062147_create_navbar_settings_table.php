<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navbar_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->default('/Images/logo/hmps-rpl-logo.png');
            $table->string('logo_alt')->default('Logo HMPS RPL');
            $table->string('brand_title')->default('HMPS RPL');
            $table->string('brand_subtitle')->default('Rekayasa Perangkat Lunak');
            $table->string('cta_label')->default('Hubungi Kami');
            $table->string('cta_contact_name')->nullable();
            $table->string('cta_whatsapp_number')->nullable();
            $table->text('cta_message')->nullable();
            $table->boolean('cta_is_active')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navbar_settings');
    }
};
