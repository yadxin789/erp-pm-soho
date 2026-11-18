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
        Schema::create('soho_warehouses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->string('code', 50)->comment('仓库编码');
            $table->string('name', 100)->comment('仓库名称');
            $table->string('type', 30)->comment('仓库类型：domestic(国内仓), overseas(海外仓), fba(FBA仓), third_party(第三方仓), virtual(虚拟仓)');
            $table->string('country_code', 10)->nullable()->comment('国家代码');
            $table->text('address')->nullable()->comment('仓库地址');
            $table->string('contact_name', 100)->nullable()->comment('联系人');
            $table->string('contact_phone', 50)->nullable()->comment('联系电话');
            $table->integer('priority')->default(0)->comment('优先级');
            $table->string('status', 20)->default('active')->comment('状态');
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'ux_23_1');
            $table->index(['tenant_id', 'status'], 'ix_23_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_warehouses');
    }
};