<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('name', 100)->comment('配送方式名称');
            $table->string('code', 50)->comment('配送方式编码');
            $table->enum('type', ['fixed', 'weight', 'quantity', 'region'])
                ->default('fixed')
                ->comment('计费类型: fixed固定 weight按重量 quantity按件数 region按地区');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0禁用');
            $table->unsignedInteger('sort')->default(0)->comment('排序');
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'ix_48_1');
            $table->index(['tenant_id', 'code'], 'ix_48_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_shipping_methods');
    }
};
