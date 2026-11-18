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
        Schema::create('soho_order_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('order_id')->unsigned()->comment('订单ID');
            $table->string('type', 30)->default('shipping')->comment('地址类型：shipping(收货), billing(账单)');
            $table->string('first_name', 100)->nullable()->comment('名');
            $table->string('last_name', 100)->nullable()->comment('姓');
            $table->string('phone', 30)->nullable()->comment('电话');
            $table->string('province', 100)->nullable()->comment('省');
            $table->string('city', 100)->nullable()->comment('市');
            $table->string('district', 100)->nullable()->comment('区/县');
            $table->string('address', 500)->nullable()->comment('详细地址');
            $table->string('postal_code', 30)->nullable()->comment('邮政编码');
            $table->string('country_code', 10)->default('CN')->comment('国家代码');
            $table->boolean('is_default')->default(false)->comment('是否默认地址');
            $table->timestamps();

            $table->index(['order_id'], 'ix_42_1');
            $table->index(['tenant_id', 'order_id'], 'ix_42_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_order_addresses');
    }
};