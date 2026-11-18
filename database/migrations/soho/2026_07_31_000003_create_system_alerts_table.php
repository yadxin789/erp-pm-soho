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
        Schema::create('soho_system_alerts', function (Blueprint $table) {
            $table->id();
            // 租户ID
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 告警标题
            $table->string('title', 200)->comment('告警标题');
            // 告警内容
            $table->text('message')->comment('告警内容');
            // 告警级别：info提示、warning警告、danger严重、critical致命
            $table->string('level', 20)->default('info')->comment('告警级别');
            // 告警来源：system系统、sync同步任务、api接口、queue队列、database数据库、disk磁盘、other其他
            $table->string('source', 30)->default('system')->comment('告警来源');
            // 告警状态：pending待处理、acknowledged已确认、resolved已解决、ignored已忽略
            $table->string('status', 20)->default('pending')->comment('告警状态');
            // 告警触发时间
            $table->timestamp('triggered_at')->useCurrent()->comment('触发时间');
            // 确认人ID
            $table->foreignId('acknowledged_by')
                ->nullable()
                
                
                ->comment('确认人ID');
            // 确认时间
            $table->timestamp('acknowledged_at')->nullable()->comment('确认时间');
            // 解决人ID
            $table->foreignId('resolved_by')
                ->nullable()
                
                
                ->comment('解决人ID');
            // 解决时间
            $table->timestamp('resolved_at')->nullable()->comment('解决时间');
            // 解决备注
            $table->text('resolution_note')->nullable()->comment('解决备注');
            // 额外数据（JSON）
            $table->json('extra_data')->nullable()->comment('额外数据(JSON)');
            // 默认包含 created_at 和 updated_at
            $table->timestamps();

            // 索引
            $table->index(['tenant_id', 'status'], 'ix_88_1');
            $table->index(['tenant_id', 'level'], 'ix_88_2');
            $table->index(['tenant_id', 'source'], 'ix_88_3');
            $table->index('triggered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_system_alerts');
    }
};
