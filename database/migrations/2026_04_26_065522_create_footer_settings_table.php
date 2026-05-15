<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->default('/Images/logo/hmps-rpl-logo.png');
            $table->string('logo_alt')->default('Logo HMPS RPL');
            $table->string('brand_title')->default('HMPS RPL');
            $table->string('brand_subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('identity_label')->nullable();
            $table->text('identity_text')->nullable();
            $table->string('navigation_title')->default('Navigasi');
            $table->string('contact_title')->default('Kontak');
            $table->string('contact_cta_label')->nullable();
            $table->string('contact_cta_url')->nullable();
            $table->string('copyright_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
    }
};