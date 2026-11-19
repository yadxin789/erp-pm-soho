<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P0-2：SOHO 客户隐私请求（数据主体权利请求：查阅 / 导出 / 删除 / 撤回授权）。
 *
 * 方案①：新建独立表承载请求全流程，避免只在页面做脱敏展示而不留痕。
 * 与 soho_sensitive_access_logs（敏感数据访问日志）互补：
 * - sensitive_access_logs 记录「谁在何时访问了谁的敏感数据」；
 * - 本表记录「客户提出的隐私诉求及处理结果」。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('soho_customer_privacy_requests')) {
            return;
        }

        Schema::create('soho_customer_privacy_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('request_no', 32)->unique();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_contact', 120)->nullable();
            // access 查阅 / export 导出 / delete 删除 / withdraw 撤回授权
            $table->string('request_type', 20)->default('access');
            $table->string('status', 20)->default('pending'); // pending / processing / completed / rejected
            $table->text('reason')->nullable();
            $table->text('handle_remark')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->string('handled_by_name', 100)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_customer_privacy_requests');
    }
};
