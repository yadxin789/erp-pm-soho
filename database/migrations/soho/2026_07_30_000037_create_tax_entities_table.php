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
        Schema::create('soho_tax_entities', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            // 主体名称
            $table->string('name', 100)->comment('主体名称');
            // 税号
            $table->string('tax_no', 50)->comment('税号');
            // 国家
            $table->string('country', 50)->comment('国家');
            // 省/州/地区
            $table->string('region', 100)->nullable()->comment('省/州/地区');
            // 城市
            $table->string('city', 100)->nullable()->comment('城市');
            // 税务类型：一般纳税人/小规模纳税人/海外主体等
            $table->string('tax_type', 30)->comment('税务类型');
            // 法人
            $table->string('legal_person', 50)->nullable()->comment('法人');
            // 联系人姓名
            $table->string('contact_name', 50)->nullable()->comment('联系人姓名');
            // 联系人电话
            $table->string('contact_phone', 30)->nullable()->comment('联系人电话');
            // 状态：active启用/inactive停用
            $table->string('status', 20)->default('active')->comment('状态');
            $table->timestamps();

            // 租户+税号唯一索引
            $table->unique(['tenant_id', 'tax_no'], 'ux_77_1');
            // 租户+状态索引
            $table->index(['tenant_id', 'status'], 'ix_77_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_tax_entities');
    }
};