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
        Schema::create('soho_product_attribute_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->bigInteger('product_attribute_id')->unsigned()->comment('商品属性ID');
            $table->string('value_name', 255)->comment('属性值名称');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            $table->index(['tenant_id', 'product_attribute_id'], 'ix_64_1');
            $table->index(['tenant_id', 'sort_order'], 'ix_64_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_product_attribute_values');
    }
};