<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 价格规则表迁移
 * 定义系统中的各种定价规则，如成本加成、竞品导向、市场导向、固定价格、区间定价等
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_price_rules', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 规则名称
            $table->string('name', 200)->comment('规则名称');
            // 规则类型：cost_plus成本加成/competitor竞品导向/market市场导向/fixed固定价格/range区间定价
            $table->string('type', 50)->comment('规则类型:cost_plus成本加成/competitor竞品导向/market市场导向/fixed固定价格/range区间定价');
            // 规则描述
            $table->text('description')->nullable()->comment('规则描述');
            // 规则参数（JSON格式），存储各类定价规则的具体参数配置
            $table->json('parameters')->nullable()->comment('规则参数(JSON)');
            // 最低价格
            $table->decimal('min_price', 12, 2)->nullable()->comment('最低价格');
            // 最高价格
            $table->decimal('max_price', 12, 2)->nullable()->comment('最高价格');
            // 是否启用
            $table->boolean('is_active')->default(true)->comment('是否启用');
            // 优先级，数值越大优先级越高
            $table->integer('priority')->default(0)->comment('优先级');
            // 创建人
            $table->foreignId('created_by')
                ->nullable()
                
                
                ->comment('创建人');
            // 创建时间和更新时间
            $table->timestamps();

            // 索引
            $table->index('tenant_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_price_rules');
    }
};
