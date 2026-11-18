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
        Schema::create('soho_webhook_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 32)->comment('平台标识');
            $table->string('event_id', 255)->comment('事件ID');
            $table->string('event_type', 64)->comment('事件类型');
            $table->enum('status', ['processing', 'success', 'failed'])->default('processing')->comment('处理状态');
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamps();

            // 复合索引：确保同一平台的同一事件ID只处理一次
            $table->unique(['platform', 'event_id'], 'ux_22_1');
            $table->index('status');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_webhook_event_logs');
    }
};
