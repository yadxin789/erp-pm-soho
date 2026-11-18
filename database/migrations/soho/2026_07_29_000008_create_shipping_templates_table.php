<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_shipping_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('name', 100)->comment('模板名称');
            $table->string('province', 50)->nullable()->comment('适用省份');
            $table->decimal('first_weight', 10, 2)->default(0)->comment('首重(kg)');
            $table->decimal('first_fee', 10, 2)->default(0)->comment('首重运费');
            $table->decimal('additional_weight', 10, 2)->default(0)->comment('续重(kg)');
            $table->decimal('additional_fee', 10, 2)->default(0)->comment('续重运费');
            $table->decimal('free_shipping_amount', 10, 2)->default(0)->comment('免运费金额');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0禁用');
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'ix_49_1');
            $table->index(['tenant_id', 'province'], 'ix_49_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_shipping_templates');
    }
};
