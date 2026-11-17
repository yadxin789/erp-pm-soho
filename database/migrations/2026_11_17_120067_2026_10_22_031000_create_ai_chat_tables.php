<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title', 200)->default('新会话');
            $table->foreignId('model_id')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'last_message_at'], 'ai_conversations_user_last_idx');
        });

        Schema::create('erp_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id');
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->longText('content')->nullable();
            $table->json('meta')->nullable()->comment('模型/供应商/耗时等附加信息');
            $table->timestamps();

            $table->index(['conversation_id', 'id'], 'ai_messages_conversation_idx');
        });

        Schema::create('erp_ai_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id');
            $table->foreignId('message_id')->nullable();
            $table->foreignId('user_id');
            $table->enum('type', ['image', 'file'])->default('file');
            $table->string('original_name', 300);
            $table->string('path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('text_extracted')->default(false);
            $table->longText('extracted_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_ai_attachments');
        Schema::dropIfExists('erp_ai_messages');
        Schema::dropIfExists('erp_ai_conversations');
    }
};
