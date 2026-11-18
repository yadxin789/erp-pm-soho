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
        Schema::create('soho_warehouse_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')
                
                
                ->comment('租户ID');
            $table->bigInteger('warehouse_id')->unsigned()->comment('仓库ID');
            $table->string('zone_name', 100)->comment('库区名称');
            $table->string('location_code', 100)->comment('库位编码');
            $table->string('location_type', 30)->default('storage')->comment('库位类型: storage存储/picking拣货/packing包装/return退货/damaged残次品');
            $table->decimal('capacity', 18, 4)->default(0)->comment('容量');
            $table->decimal('current_usage', 18, 4)->default(0)->comment('当前使用量');
            $table->string('status', 20)->default('active')->comment('状态: active启用/inactive禁用');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->unique(['tenant_id', 'warehouse_id', 'location_code'], 'ux_69_1');
            $table->index(['tenant_id', 'zone_name'], 'ix_69_2');
            $table->index(['tenant_id', 'location_type'], 'ix_69_3');
            $table->index(['tenant_id', 'status'], 'ix_69_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_warehouse_locations');
    }
};