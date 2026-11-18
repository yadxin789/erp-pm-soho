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
        Schema::create('soho_exchange_rates', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 源币种
            $table->string('from_currency', 10)->comment('源币种');
            // 目标币种
            $table->string('to_currency', 10)->comment('目标币种');
            // 汇率
            $table->decimal('rate', 12, 6)->comment('汇率');
            // 汇率来源：manual手动/api接口/平台同步
            $table->string('rate_source', 50)->nullable()->comment('汇率来源');
            // 汇率日期
            $table->date('rate_date')->comment('汇率日期');
            // 更新人ID
            $table->foreignId('updated_by')
                ->nullable()
                
                
                ->comment('更新人ID');
            $table->timestamps();

            // 租户+源币种+目标币种唯一索引
            $table->unique(['tenant_id', 'from_currency', 'to_currency'], 'ux_76_1');
            // 汇率日期索引
            $table->index('rate_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_exchange_rates');
    }
};