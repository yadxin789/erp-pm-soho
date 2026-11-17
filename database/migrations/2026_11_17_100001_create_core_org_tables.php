<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：departments（三棵组织树合成一棵，含 module 判别列）+ positions（erp 口径）
 * 依据：01-目标数据库字典 §2.2 / §2.3
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            // erp/pm 有 code；soho 无 → 迁移期回填 SOHO-D{旧id}
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('short_name', 50)->nullable();
            // erp description / soho remark 合并
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            // 部门 → 模块映射：erp / pm / soho / hr / core；空则向上继承父级
            $table->string('module', 20)->nullable()->index();
            // pm 专用语义 project / non-project
            $table->string('type', 20)->nullable();
            $table->integer('sort_order')->default(0);
            // erp active / soho status 统一为 1 启用 / 0 停用
            $table->tinyInteger('status')->default(1)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('departments')->nullOnDelete();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->unsignedBigInteger('department_id')->nullable()->index();
            // 高管 / 经理 / 主管 / 专员 / 员工
            $table->string('level', 20)->nullable();
            $table->unsignedInteger('headcount')->nullable();
            // 对应 spatie 角色名（角色≠岗位，仅作新建用户默认值）
            $table->string('default_role', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        // users 的外键在两张表建好后再补，避免前向依赖
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
        });
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
