<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：system_alerts（合并 erp / soho 同名表）
 * 依据：01-目标数据库字典 §2.7
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('title', 200);
            $table->text('message');
            // info / warning / danger / critical
            $table->string('level', 20)->default('warning')->index();
            $table->string('source', 50)->nullable();
            // open / acknowledged / resolved
            $table->string('status', 20)->default('open')->index();
            $table->timestamp('triggered_at');
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
    }
};
