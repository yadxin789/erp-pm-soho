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
        Schema::create('soho_promotion_platform_mappings', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 促销ID
            $table->foreignId('promotion_id')
                
                
                ->comment('促销ID');
            // 平台编码：taobao淘宝/jd京东/amazon亚马逊/pdd拼多多等
            $table->string('platform', 50)->comment('平台编码');
            // 平台侧促销ID
            $table->string('platform_promotion_id', 100)->nullable()->comment('平台侧促销ID');
            // 店铺ID
            $table->foreignId('shop_id')
                ->nullable()
                
                
                ->comment('店铺ID');
            // 同步状态：pending待同步/syncing同步中/synced已同步/failed同步失败
            $table->string('sync_status', 20)->default('pending')->comment('同步状态');
            // 最近同步时间
            $table->timestamp('last_sync_at')->nullable()->comment('最近同步时间');
            // 平台原始数据JSON
            $table->json('raw_data')->nullable()->comment('平台原始数据(JSON)');
            $table->timestamps();

            // 租户+促销+平台唯一索引
            $table->unique(['tenant_id', 'promotion_id', 'platform'], 'ux_80_1');
            // 租户+平台索引
            $table->index(['tenant_id', 'platform'], 'ix_80_2');
            // 租户+同步状态索引
            $table->index(['tenant_id', 'sync_status'], 'ix_80_3');
            // 最近同步时间索引
            $table->index('last_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_promotion_platform_mappings');
    }
};
