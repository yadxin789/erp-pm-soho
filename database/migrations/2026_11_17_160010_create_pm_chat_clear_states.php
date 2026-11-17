<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-03：PM 的聊天清屏状态独立建表。
 *
 * Core/ERP 的 chat_clear_states 是 module/room_key/cleared_at 口径，
 * 与 PM 的 user_id/project_id/chat_type/conversation_key 口径冲突，
 * 故 PM 使用 pm_chat_clear_states（源：PM 2026_07_06_000001_create_chat_clear_states_table）。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pm_chat_clear_states')) {
            return;
        }

        Schema::create('pm_chat_clear_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->string('chat_type', 20);
            $table->unsignedBigInteger('other_user_id')->nullable();
            $table->string('conversation_key', 64);
            $table->timestamp('cleared_at');
            $table->timestamps();

            $table->unique(['user_id', 'project_id', 'chat_type', 'conversation_key'], 'pm_chat_clear_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_chat_clear_states');
    }
};
