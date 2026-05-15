<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_approver_change_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requested_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('current_approver_id')
                ->nullable()
                ->constrained('security_approvers')
                ->nullOnDelete();

            $table->string('token', 100)->unique();

            $table->string('old_name')->nullable();
            $table->string('old_position')->nullable();
            $table->string('old_whatsapp_number', 30)->nullable();
            $table->string('old_normalized_whatsapp_number', 30)->nullable();

            $table->string('new_name');
            $table->string('new_position')->default('Ketua HMPS');
            $table->string('new_whatsapp_number', 30);
            $table->string('new_normalized_whatsapp_number', 30);

            $table->string('status', 30)->default('pending');

            $table->text('request_reason')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('reject_reason')->nullable();

            $table->string('processed_by_name')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->string('ip_address', 80)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['requested_by_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_approver_change_requests');
    }
};
