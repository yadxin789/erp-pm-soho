<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERP 私有审批流表（与 Core 统一审批中心 approval_* 并存）。
 * 来源：D:\laravel12-erp\database\migrations\2026_10_16_175000_create_approval_flows_table.php
 * 说明：ERP 原有 ApprovalFlow / ApprovalFlowStep / ApprovalTask 模型结构独立，
 *       B-02 阶段保留其原逻辑，表名加 erp_ 前缀避免与 Core 统一审批中心冲突。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('document_type', 50);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('erp_approval_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_flow_id');
            $table->integer('step_order');
            $table->string('name', 100);
            $table->string('approver_type', 30);
            $table->string('approver_value');
            $table->timestamps();
        });

        Schema::create('erp_approval_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 50);
            $table->unsignedBigInteger('document_id');
            $table->integer('step_order');
            $table->string('step_name', 100);
            $table->unsignedBigInteger('approver_id');
            $table->string('status', 20)->default('pending');
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['document_type', 'document_id']);
            $table->index(['approver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_approval_tasks');
        Schema::dropIfExists('erp_approval_flow_steps');
        Schema::dropIfExists('erp_approval_flows');
    }
};
