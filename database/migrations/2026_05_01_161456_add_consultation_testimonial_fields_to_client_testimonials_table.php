<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_testimonials', function (Blueprint $table) {
            if (! Schema::hasColumn('client_testimonials', 'role')) {
                if (Schema::hasColumn('client_testimonials', 'name')) {
                    $table->string('role')->nullable()->after('name');
                } else {
                    $table->string('role')->nullable();
                }
            }

            if (! Schema::hasColumn('client_testimonials', 'source_page')) {
                if (Schema::hasColumn('client_testimonials', 'service_type')) {
                    $table->string('source_page')->default('layanan_jasa')->index()->after('service_type');
                } else {
                    $table->string('source_page')->default('layanan_jasa')->index();
                }
            }

            if (! Schema::hasColumn('client_testimonials', 'rating')) {
                if (Schema::hasColumn('client_testimonials', 'source_page')) {
                    $table->unsignedTinyInteger('rating')->default(5)->after('source_page');
                } else {
                    $table->unsignedTinyInteger('rating')->default(5);
                }
            }

            if (! Schema::hasColumn('client_testimonials', 'emoji')) {
                $table->string('emoji', 20)->nullable()->after('rating');
            }

            if (! Schema::hasColumn('client_testimonials', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index()->after('is_approved');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('client_testimonials', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('client_testimonials', 'emoji')) {
                $table->dropColumn('emoji');
            }

            if (Schema::hasColumn('client_testimonials', 'rating')) {
                $table->dropColumn('rating');
            }

            if (Schema::hasColumn('client_testimonials', 'source_page')) {
                $table->dropColumn('source_page');
            }

            if (Schema::hasColumn('client_testimonials', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};