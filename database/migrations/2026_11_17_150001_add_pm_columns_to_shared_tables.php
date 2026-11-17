<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-03 共享表补 PM 字段：
 *  - users.is_active：PM 原 users 表用 is_active 表示启停（Core 用 status），
 *    PM 控制器/查询直接用 where('is_active', 1)，故补列并在模型层双向同步。
 *  - notifications.deleted_at：PM Notification 模型使用 SoftDeletes。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('status');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
