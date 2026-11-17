<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：notifications（三源并集，统一收口，含 module 判别列）+ notification_templates
 * 依据：01-目标数据库字典 §2.5
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('sender_id')->nullable()->index();
            // erp / pm / soho / core —— 前端分组与跳转依据
            $table->string('module', 20)->nullable()->index();
            // 统一语义类型；原始值保留在 data 里
            $table->string('type', 40)->default('system');
            $table->string('title', 200);
            // erp message / pm content / soho content
            $table->text('content')->nullable();
            // erp data / soho data_json
            $table->json('data')->nullable();
            $table->string('link', 255)->nullable();
            $table->string('priority', 20)->default('normal');
            // pm 用 is_read，erp/soho 用 read_at，两者并存
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // soho 的通知模板收进 Core，供三模块共用
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            $table->string('code', 60);
            $table->string('title', 200);
            $table->text('content')->nullable();
            $table->string('type', 40)->default('system');
            $table->json('variables')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notifications');
    }
};
