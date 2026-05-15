<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('management_divisions', function (Blueprint $table) {
            $table->index('sort_order', 'management_divisions_sort_order_idx');
        });

        Schema::table('management_members', function (Blueprint $table) {
            $table->index(
                ['category', 'sort_order'],
                'management_members_category_sort_order_idx'
            );

            $table->index(
                ['management_division_id', 'category', 'sort_order'],
                'management_members_division_category_sort_order_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('management_members', function (Blueprint $table) {
            $table->dropIndex('management_members_category_sort_order_idx');
            $table->dropIndex('management_members_division_category_sort_order_idx');
        });

        Schema::table('management_divisions', function (Blueprint $table) {
            $table->dropIndex('management_divisions_sort_order_idx');
        });
    }
};