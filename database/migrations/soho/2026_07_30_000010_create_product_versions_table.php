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
        Schema::create('soho_product_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->bigInteger('product_id')->unsigned()->comment('商品ID');
            $table->integer('version_no')->comment('版本号');
            $table->string('title', 255)->comment('版本标题');
            $table->longText('description')->nullable()->comment('版本描述');
            $table->decimal('price', 18, 4)->default(0)->comment('版本价格');
            $table->string('status', 30)->default('draft')->comment('状态: draft草稿/published已发布/deprecated已废弃');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->bigInteger('created_by')->unsigned()->nullable()->comment('创建人ID');
            $table->timestamps();

            $table->index(['tenant_id', 'product_id'], 'ix_63_1');
            $table->index(['tenant_id', 'status'], 'ix_63_2');
            $table->index(['tenant_id', 'version_no'], 'ix_63_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_product_versions');
    }
};