<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100)->comment('事件类型');
            $table->string('model_type', 100)->comment('模型类型');
            $table->unsignedBigInteger('model_id')->comment('模型ID');
            $table->foreignId('user_id')
                ->nullable()
                
                
                ->comment('用户ID');
            $table->json('payload_json')->nullable()->comment('负载数据');
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            $table->index(['model_type', 'model_id'], 'ix_53_1');
            $table->index('event_type');
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_event_logs');
    }
};