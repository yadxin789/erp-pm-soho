<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('name', 100)->comment('税率名称');
            $table->string('country_code', 10)->default('CN')->comment('国家代码');
            $table->string('province', 50)->nullable()->comment('省份');
            $table->decimal('rate', 5, 2)->default(0)->comment('税率(%)');
            $table->tinyInteger('is_compound')->default(0)->comment('是否复合税率: 1是 0否');
            $table->tinyInteger('included_in_price')->default(0)->comment('是否含税: 1是 0否');
            $table->unsignedInteger('priority')->default(0)->comment('优先级');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0禁用');
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'ix_47_1');
            $table->index(['tenant_id', 'country_code', 'province'], 'ix_47_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_tax_rates');
    }
};
