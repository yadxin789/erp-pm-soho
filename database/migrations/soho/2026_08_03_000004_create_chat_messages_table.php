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
        Schema::create('soho_chat_messages', function (Blueprint $table) {
            $table->id();
            // 发送消息的用户
            $table->foreignId('user_id');
            // 接收者ID（群聊可为空）
            $table->foreignId('receiver_id')->nullable();
            // 是否是私聊
            $table->boolean('is_private')->default(false);
            // 消息内容
            $table->text('content');
            // 附件路径（可选）
            $table->string('file_path', 255)->nullable();
            // 附件文件名
            $table->string('file_name', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // 索引优化查询
            $table->index(['user_id', 'created_at'], 'ix_105_1');
            $table->index(['receiver_id', 'is_private'], 'ix_105_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_chat_messages');
    }
};
