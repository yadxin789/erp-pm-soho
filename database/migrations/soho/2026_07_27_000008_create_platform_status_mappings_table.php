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
        Schema::create('soho_platform_status_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('platform', 30)->comment('平台代码：taobao, jd, amazon, pinduoduo');
            $table->string('object_type', 30)->comment('对象类型：order(订单), after_sale(售后)');
            $table->string('platform_status', 100)->comment('平台原始状态');
            $table->string('unified_status', 40)->comment('统一状态');
            $table->string('description', 255)->nullable()->comment('状态描述');
            $table->timestamps();

            $table->unique(['platform', 'object_type', 'platform_status'], 'ux_29_1');
            $table->index(['platform', 'object_type'], 'ix_29_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_platform_status_mappings');
    }
};