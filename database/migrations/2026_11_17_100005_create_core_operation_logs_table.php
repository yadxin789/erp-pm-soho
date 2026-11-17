<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：operation_logs（合并 erp audit_logs + pm/soho operation_logs）
 * 依据：01-目标数据库字典 §2.7
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            // 冗余用户名，避免账号删除后日志失去可读性
            $table->string('user_name', 100)->nullable();
            $table->string('action', 100)->index();
            $table->string('target_type', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            // pm/soho 的 changes / description / extra
            $table->json('payload')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
