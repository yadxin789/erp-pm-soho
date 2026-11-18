<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_finance_settles', function (Blueprint $table) {
            $table->id();
            $table->string('settle_no', 50)->unique('ux_4_1');
            $table->foreignId('shop_id');
            $table->decimal('amount', 12, 2);
            $table->enum('settle_method', ['bank', 'alipay', 'wechat', 'other']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->foreignId('operator_id');
            $table->string('remark', 500)->nullable();
            $table->timestamps();
            $table->index('shop_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_finance_settles');
    }
};