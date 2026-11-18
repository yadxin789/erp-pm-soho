<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_order_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->foreignId('order_id')
                
                
                ->comment('订单ID');
            $table->string('type', 20)->comment('调整类型: discount折扣/adjustment调整');
            $table->decimal('amount', 12, 2)->default(0)->comment('调整金额');
            $table->string('reason', 500)->nullable()->comment('调整原因');
            $table->foreignId('adjusted_by')
                ->nullable()
                
                
                ->comment('调整人');
            $table->timestamps();

            $table->index(['tenant_id', 'order_id'], 'ix_55_1');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_order_adjustments');
    }
};