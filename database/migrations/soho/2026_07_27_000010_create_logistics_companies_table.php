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
        Schema::create('soho_logistics_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->string('name', 100)->comment('物流公司名称');
            $table->string('code', 50)->comment('物流编码');
            $table->json('platform_codes')->nullable()->comment('各平台物流编码映射');
            $table->json('channel_config')->nullable()->comment('渠道配置');
            $table->decimal('first_weight', 18, 4)->nullable()->comment('首重');
            $table->decimal('additional_weight', 18, 4)->nullable()->comment('续重');
            $table->boolean('is_international')->default(false)->comment('是否支持国际运输');
            $table->string('status', 20)->default('active')->comment('状态');
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'ux_31_1');
            $table->index(['tenant_id', 'status'], 'ix_31_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_logistics_companies');
    }
};