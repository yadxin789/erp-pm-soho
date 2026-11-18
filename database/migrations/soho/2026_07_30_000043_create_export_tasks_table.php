<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_export_tasks', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 任务编号
            $table->string('task_no', 50)->unique('ux_81_1')->comment('任务编号');
            // 任务类型：order订单/product商品/inventory库存/finance财务/customer客户/report报表
            $table->string('task_type', 30)->comment('任务类型');
            // 导出参数JSON
            $table->json('params')->nullable()->comment('导出参数(JSON)');
            // 状态：pending待处理/processing处理中/completed已完成/failed失败
            $table->string('status', 20)->default('pending')->comment('状态');
            // 导出文件路径
            $table->string('file_path', 500)->nullable()->comment('导出文件路径');
            // 文件大小（字节）
            $table->unsignedBigInteger('file_size')->default(0)->comment('文件大小(字节)');
            // 导出行数
            $table->unsignedInteger('row_count')->default(0)->comment('导出行数');
            // 操作人ID
            $table->foreignId('operator_id')
                ->nullable()
                
                
                ->comment('操作人ID');
            // 任务完成时间
            $table->timestamp('completed_at')->nullable()->comment('任务完成时间');
            // 文件过期时间
            $table->timestamp('expires_at')->nullable()->comment('文件过期时间');
            // 默认包含 created_at 和 updated_at
            $table->timestamps();

            // 租户+任务类型索引
            $table->index(['tenant_id', 'task_type'], 'ix_81_2');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_81_3');
            // 过期时间索引
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_export_tasks');
    }
};
