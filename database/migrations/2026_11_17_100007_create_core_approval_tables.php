<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：审批四表（erp approval_flows/flow_steps/tasks + soho approval_flows/steps/instances 合并）
 * 依据：B-01 TC-02、erp 与 soho 的实际表结构
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            $table->string('name', 100);
            // 单据类型键，如 sales_order / purchase_order / product_publish
            $table->string('document_type', 50)->index();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_flow_id')->index();
            $table->integer('step_order')->default(1);
            $table->string('name', 100);
            // role / user / position / department
            $table->string('approver_type', 30)->default('role');
            // 角色名 / 用户 id / 岗位 id 等，按 approver_type 解释
            $table->string('approver_value', 255);
            $table->timestamps();

            $table->foreign('approval_flow_id')->references('id')->on('approval_flows')->cascadeOnDelete();
        });

        Schema::create('approval_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_flow_id')->nullable()->index();
            $table->string('module', 20)->nullable()->index();
            $table->string('document_type', 50)->index();
            $table->unsignedBigInteger('document_id');
            $table->string('title', 200)->nullable();
            $table->unsignedBigInteger('applicant_id')->nullable()->index();
            $table->integer('current_step')->default(1);
            // pending / approving / approved / rejected / cancelled
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });

        Schema::create('approval_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_instance_id')->nullable()->index();
            $table->string('module', 20)->nullable()->index();
            $table->string('document_type', 50);
            $table->unsignedBigInteger('document_id');
            $table->integer('step_order')->default(1);
            $table->string('step_name', 100)->nullable();
            $table->unsignedBigInteger('approver_id')->index();
            // pending / approved / rejected / skipped
            $table->string('status', 20)->default('pending')->index();
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_tasks');
        Schema::dropIfExists('approval_instances');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_flows');
    }
};
