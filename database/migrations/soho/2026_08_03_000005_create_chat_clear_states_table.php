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
        Schema::create('soho_chat_clear_states', function (Blueprint $table) {
            $table->id();
            // 执行清空操作的用户
            $table->foreignId('user_id');
            // 聊天类型：group 群聊 / private 私聊
            $table->string('chat_type', 20)->default('group');
            // 会话标识：群聊为空，私聊为 user_{对方用户ID}
            $table->string('conversation_key', 100)->nullable();
            // 私聊时的对方用户ID
            $table->foreignId('other_user_id')->nullable();
            // 清空时间点（只显示此时间之后的消息）
            $table->timestamp('cleared_at');
            $table->timestamps();

            // 索引：一个用户在一个会话中只有一条清空状态记录
            $table->unique(['user_id', 'chat_type', 'conversation_key'], 'uniq_user_chat_clear');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_chat_clear_states');
    }
};
