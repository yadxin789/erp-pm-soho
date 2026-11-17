<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 共享 departments 表补 ERP 需要的字段。
 * ERP 原 departments 表含 manager_id（部门负责人），Core 统一部门表未包含，
 * 按「业务重叠表合并为一套」原则补列，不新建重复表。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->after('parent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'manager_id')) {
                $table->dropColumn('manager_id');
            }
        });
    }
};
