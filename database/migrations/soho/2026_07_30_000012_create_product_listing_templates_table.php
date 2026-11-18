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
        Schema::create('soho_product_listing_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->string('name', 255)->comment('模板名称');
            $table->string('platform', 50)->comment('平台标识');
            $table->bigInteger('shop_id')->unsigned()->nullable()->comment('店铺ID');
            $table->bigInteger('category_id')->unsigned()->nullable()->comment('类目ID');
            $table->json('template_data')->nullable()->comment('模板数据(JSON)');
            $table->string('status', 20)->default('active')->comment('状态: active启用/inactive禁用');
            $table->bigInteger('created_by')->unsigned()->nullable()->comment('创建人ID');
            $table->timestamps();

            $table->index(['tenant_id', 'platform'], 'ix_65_1');
            $table->index(['tenant_id', 'shop_id'], 'ix_65_2');
            $table->index(['tenant_id', 'status'], 'ix_65_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_product_listing_templates');
    }
};