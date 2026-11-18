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
        Schema::create('soho_customer_tags', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 标签名称
            $table->string('name', 50)->comment('标签名称');
            // 标签颜色（十六进制）
            $table->string('color', 20)->nullable()->comment('标签颜色');
            // 标签类型：manual手动标签/auto自动标签
            $table->string('type', 20)->default('manual')->comment('标签类型');
            // 自动标签条件配置JSON
            $table->json('condition_config')->nullable()->comment('自动标签条件配置(JSON)');
            // 创建人ID
            $table->foreignId('created_by')
                ->nullable()
                
                
                ->comment('创建人ID');
            $table->timestamps();

            // 租户+名称唯一索引
            $table->unique(['tenant_id', 'name'], 'ux_79_1');
            // 租户+类型索引
            $table->index(['tenant_id', 'type'], 'ix_79_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_customer_tags');
    }
};
