<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soho_shops', function (Blueprint $table) {
            $table->index('status');
            $table->index('platform');
        });

        Schema::table('soho_order_after_sales', function (Blueprint $table) {
            $table->index('status');
            $table->index('platform');
            $table->index('shop_id');
        });

        Schema::table('soho_system_goods', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('soho_sync_task_logs', function (Blueprint $table) {
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::table('soho_shops', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['platform']);
        });

        Schema::table('soho_order_after_sales', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['platform']);
            $table->dropIndex(['shop_id']);
        });

        Schema::table('soho_system_goods', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('soho_sync_task_logs', function (Blueprint $table) {
            $table->dropIndex(['shop_id']);
        });
    }
};
