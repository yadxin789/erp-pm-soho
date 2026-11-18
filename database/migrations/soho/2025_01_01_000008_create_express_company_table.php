<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_express_company', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('快递公司名称');
            $table->string('code', 50)->unique('ux_11_1')->comment('统一快递编码');
            $table->string('taobao_code', 50)->nullable()->comment('淘宝物流编码');
            $table->string('jd_code', 50)->nullable()->comment('京东物流编码');
            $table->string('pdd_code', 50)->nullable()->comment('拼多多物流编码');
            $table->string('amazon_code', 50)->nullable()->comment('亚马逊物流编码');
            $table->string('website', 200)->nullable()->comment('官网地址');
            $table->string('phone', 30)->nullable()->comment('客服电话');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0停用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_express_company');
    }
};
