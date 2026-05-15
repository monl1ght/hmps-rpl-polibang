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
        Schema::table('management_members', function (Blueprint $table) {
            $table->text('task_description')
                ->nullable()
                ->after('position')
                ->comment('Tupoksi, tugas, dan tanggung jawab pengurus inti atau pengurus terkait.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('management_members', function (Blueprint $table) {
            $table->dropColumn('task_description');
        });
    }
};
