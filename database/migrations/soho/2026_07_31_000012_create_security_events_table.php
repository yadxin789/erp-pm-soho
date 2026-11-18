<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 安全事件表迁移
 * 记录系统安全事件（如攻击、漏洞、异常行为等）
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_security_events', function (Blueprint $table) {
            $table->id();
            // 事件类型：attack攻击/bruteforce暴力破解/anomaly异常/leak泄露/other其他
            $table->string('event_type', 50)->comment('事件类型');
            // 严重程度：low低/medium中/high高/critical严重
            $table->string('severity', 20)->default('medium')->comment('严重程度');
            // 事件标题
            $table->string('title', 255)->comment('事件标题');
            // 事件描述
            $table->text('description')->nullable()->comment('事件描述');
            // 关联用户ID
            $table->foreignId('user_id')
                ->nullable()
                
                
                ->comment('关联用户ID');
            // 来源IP
            $table->string('ip_address', 45)->nullable()->comment('来源IP');
            // 状态：open待处理/investigating调查中/resolved已解决/false_positive误报
            $table->string('status', 30)->default('open')->comment('状态');
            // 处理人ID
            $table->foreignId('handled_by')
                ->nullable()
                
                
                ->comment('处理人ID');
            // 处理时间
            $table->timestamp('handled_at')->nullable()->comment('处理时间');
            // 处理备注
            $table->text('handle_note')->nullable()->comment('处理备注');
            // 发生时间
            $table->timestamp('occurred_at')->useCurrent()->comment('发生时间');
            $table->timestamps();

            $table->index('event_type');
            $table->index('severity');
            $table->index('status');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_security_events');
    }
};