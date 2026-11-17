<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：聊天与语音（erp/pm/soho 三套重复实现收成一套）
 * 依据：01-目标数据库字典 §2.6
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            // direct / department / group / project
            $table->string('room_type', 20)->default('group');
            // 部门 id / 项目 id / 群组 id；direct 时为空
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('sender_id')->index();
            $table->text('content')->nullable();
            // text / image / file / voice
            $table->string('msg_type', 20)->default('text');
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_type', 'room_id', 'id']);
        });

        Schema::create('chat_clear_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('module', 20)->nullable()->index();
            // room_key 拼接规则统一：{module}:{room_type}:{room_id}(:{peer_id})
            $table->string('room_key', 120);
            $table->timestamp('cleared_at');
            $table->timestamps();

            $table->unique(['user_id', 'room_key']);
        });

        Schema::create('voice_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            $table->string('name', 100);
            // group / department / project / direct
            $table->string('type', 20)->default('group');
            $table->unsignedBigInteger('owner_id')->index();
            $table->string('status', 20)->default('open');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('voice_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            // host / speaker / listener
            $table->string('role', 20)->default('speaker');
            $table->timestamps();

            $table->unique(['room_id', 'user_id']);
        });

        Schema::create('voice_signals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->index();
            $table->unsignedBigInteger('from_user')->index();
            // 为空表示广播（如房间内成员列表变更）
            $table->unsignedBigInteger('to_user')->nullable()->index();
            // offer / answer / candidate / join / leave 等
            $table->string('type', 20);
            $table->text('payload');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_signals');
        Schema::dropIfExists('voice_participants');
        Schema::dropIfExists('voice_rooms');
        Schema::dropIfExists('chat_clear_states');
        Schema::dropIfExists('chat_messages');
    }
};
