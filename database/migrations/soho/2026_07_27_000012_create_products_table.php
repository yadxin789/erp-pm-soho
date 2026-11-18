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
        Schema::create('soho_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->string('product_code', 100)->comment('商品编码');
            $table->string('name', 255)->comment('商品名称');
            $table->string('subtitle', 255)->nullable()->comment('商品副标题');
            $table->bigInteger('brand_id')->unsigned()->nullable()->comment('品牌ID');
            $table->bigInteger('category_id')->unsigned()->nullable()->comment('类目ID');
            $table->string('status', 30)->default('draft')->comment('状态：draft(草稿), pending(待审核), active(在售), inactive(下架), archived(归档), error(发布错误)');
            $table->longText('description')->nullable()->comment('商品描述');
            $table->decimal('cost_price', 18, 4)->default(0)->comment('成本价');
            $table->decimal('retail_price', 18, 4)->default(0)->comment('吊牌价');
            $table->decimal('weight', 18, 4)->nullable()->comment('重量');
            $table->json('attributes')->nullable()->comment('扩展属性');
            $table->bigInteger('created_by')->unsigned()->nullable()->comment('创建人ID');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'product_code'], 'ux_33_1');
            $table->index(['tenant_id', 'status'], 'ix_33_2');
            $table->index(['tenant_id', 'category_id'], 'ix_33_3');
            $table->index(['tenant_id', 'brand_id'], 'ix_33_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_products');
    }
};