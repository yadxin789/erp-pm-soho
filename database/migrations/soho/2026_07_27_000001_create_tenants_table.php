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
        Schema::create('soho_tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('租户名称');
            $table->string('code', 50)->unique('ux_21_1')->comment('租户编码');
            $table->string('status', 20)->default('active')->comment('状态');
            $table->text('logo')->nullable()->comment('租户Logo');
            $table->string('contact_name', 50)->nullable()->comment('联系人');
            $table->string('contact_phone', 20)->nullable()->comment('联系电话');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->text('address')->nullable()->comment('地址');
            $table->json('config')->nullable()->comment('配置信息');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_tenants');
    }
};