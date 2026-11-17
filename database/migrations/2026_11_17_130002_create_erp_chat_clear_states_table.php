<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERP 私有聊天清屏状态表（ERP 原 ChatClearState 结构）。
 * 说明：Core 统一聊天使用 chat_clear_states(module/room_key)，字段语义不同，
 *      B-02 阶段保留 ERP 原结构，融合阶段（B-05）再统一。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_chat_clear_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('chat_type', 20);
            $table->string('conversation_key', 100);
            $table->unsignedBigInteger('other_user_id')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'chat_type', 'conversation_key'], 'ix_erp_ccs_user_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_chat_clear_states');
    }
};
