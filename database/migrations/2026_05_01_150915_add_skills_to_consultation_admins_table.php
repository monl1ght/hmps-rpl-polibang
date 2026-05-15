<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_admins', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_admins', 'skills')) {
                $table->json('skills')->nullable()->after('badge');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultation_admins', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_admins', 'skills')) {
                $table->dropColumn('skills');
            }
        });
    }
};
