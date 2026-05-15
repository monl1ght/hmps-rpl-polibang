<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_approvers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('position')->default('Ketua HMPS');
            $table->string('whatsapp_number', 30);
            $table->string('normalized_whatsapp_number', 30);
            $table->string('role')->default('primary_approver');

            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->timestamps();

            $table->index(['is_primary', 'is_active']);
            $table->index('normalized_whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_approvers');
    }
};
