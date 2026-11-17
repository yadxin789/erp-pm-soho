<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：spatie 权限 5 表（含 erp 扩展列 display_name/group/display，guard 统一 web）
 *      + user_module_access（跨模块例外授权）+ user_source_accounts（迁移溯源）
 * 依据：01-目标数据库字典 §2.4 / §2.7，D7 / D15
 *
 * 注意：权限名统一 {module}.{resource}.{action}；guard 一律 web。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 125);
            $table->string('guard_name', 125)->default('web');
            // erp 扩展列
            $table->string('display_name', 100)->nullable();
            $table->string('group', 50)->nullable();
            $table->boolean('display')->default(true);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 125);
            $table->string('guard_name', 125)->default('web');
            $table->string('display_name', 100)->nullable();
            $table->string('group', 50)->nullable();
            $table->boolean('display')->default(true);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            // 统一为 App\Core\Models\User
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        // 跨模块例外授权：users.module 之外，允许临时进别的模块
        Schema::create('user_module_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('module', 20);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // 迁移溯源：目标用户 ← 哪个源系统的哪个账号（P5 数据迁移与回滚用）
        Schema::create('user_source_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('module', 20)->index();
            $table->unsignedBigInteger('source_user_id');
            $table->string('source_identifier', 150)->nullable();
            $table->timestamp('migrated_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['module', 'source_user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_source_accounts');
        Schema::dropIfExists('user_module_access');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
