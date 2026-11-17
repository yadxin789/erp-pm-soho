<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERP 模块私聊/群聊消息表（ERP 原 ChatMessage 结构：user_id / receiver_id / is_private）。
 * 说明：Core 全局聊天使用 chat_messages（room_type / room_id / sender_id 结构），
 *      两者字段语义不同，B-02 阶段先保留 ERP 原表结构，融合阶段（B-05）再统一。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->boolean('is_private')->default(false);
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_private', 'created_at']);
            $table->index('receiver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_chat_messages');
    }
};
