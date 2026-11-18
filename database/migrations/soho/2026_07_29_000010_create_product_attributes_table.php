<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('name', 100)->comment('属性名称');
            $table->string('code', 50)->comment('属性编码');
            $table->string('type', 30)->comment('属性类型: text文本/number数字/select单选/multi_select多选/date日期');
            $table->json('options_json')->nullable()->comment('选项配置(select/multi_select类型使用)');
            $table->tinyInteger('sort')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0禁用');
            $table->timestamps();

            $table->index(['tenant_id', 'code'], 'ix_51_1');
            $table->index(['tenant_id', 'status'], 'ix_51_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_product_attributes');
    }
};
