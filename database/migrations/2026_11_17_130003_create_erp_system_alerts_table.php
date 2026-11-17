<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ERP 私有系统告警表（ERP 原结构：fingerprint / occurrences / last_triggered_at 等）。
 * 来源：D:\laravel12-erp\database\migrations\2026_10_23_000001_create_system_alerts_table.php
 * 说明：Core 的 system_alerts 采用统一告警口径（source/status triggered_at），
 *      ERP 健康度页依赖自己的指纹/冷却字段，B-02 阶段先保留 ERP 原表。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 190)->unique();
            $table->string('source', 64);
            $table->string('level', 16)->default('warning');
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->string('status', 16)->default('firing');
            $table->unsignedInteger('occurrences')->default(1);
            $table->unsignedInteger('notify_count')->default(0);
            $table->timestamp('first_triggered_at')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'level']);
            $table->index(['source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_system_alerts');
    }
};
