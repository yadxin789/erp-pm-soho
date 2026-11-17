<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 共享 projects 表补 ERP 字段。
 * ERP 原 projects 表含 manager_id（项目经理），PM 统一项目表用 creator_id 表达创建人，
 * 按「业务重叠表合并为一套」原则补列，不新建重复表。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->after('creator_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'manager_id')) {
                $table->dropColumn('manager_id');
            }
        });
    }
};
