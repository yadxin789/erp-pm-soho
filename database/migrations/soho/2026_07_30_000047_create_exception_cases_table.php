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
        Schema::create('soho_exception_cases', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 异常编码
            $table->string('exception_code', 50)->comment('异常编码');
            // 严重等级：low低/medium中/high高/critical严重
            $table->string('severity', 20)->default('medium')->comment('严重等级');
            // 状态：new新的/assigned已分配/retrying重试中/waiting_manual待人工处理/resolved已解决/ignored已忽略/closed已关闭
            $table->string('status', 30)->default('new')->comment('处理状态');
            // 店铺ID
            $table->foreignId('shop_id')
                ->nullable()
                
                
                ->comment('店铺ID');
            // 订单ID
            $table->foreignId('order_id')
                ->nullable()
                
                
                ->comment('订单ID');
            // 任务ID（如导出任务、同步任务等）
            $table->string('task_id', 100)->nullable()->comment('任务ID');
            // 错误信息
            $table->text('error_message')->nullable()->comment('错误信息');
            // 堆栈跟踪
            $table->text('stack_trace')->nullable()->comment('堆栈跟踪');
            // 原始请求数据JSON
            $table->json('raw_request')->nullable()->comment('原始请求数据(JSON)');
            // 原始响应数据JSON
            $table->json('raw_response')->nullable()->comment('原始响应数据(JSON)');
            // 处理人ID
            $table->foreignId('handler_id')
                ->nullable()
                
                
                ->comment('处理人ID');
            // 解决时间
            $table->timestamp('resolved_at')->nullable()->comment('解决时间');
            // 解决备注
            $table->string('resolution_note', 500)->nullable()->comment('解决备注');
            $table->timestamps();

            // 租户+异常编码索引
            $table->index(['tenant_id', 'exception_code'], 'ix_85_1');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_85_2');
            // 租户+严重等级索引
            $table->index(['tenant_id', 'severity'], 'ix_85_3');
            // 租户+店铺索引
            $table->index(['tenant_id', 'shop_id'], 'ix_85_4');
            // 租户+订单索引
            $table->index(['tenant_id', 'order_id'], 'ix_85_5');
            // 解决时间索引
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_exception_cases');
    }
};
