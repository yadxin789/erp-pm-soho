<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_finance_reconciles', function (Blueprint $table) {
            $table->id();
            $table->string('reconcile_no', 50)->unique('ux_3_1');
            $table->foreignId('shop_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('operator_id');
            $table->string('remark', 500)->nullable();
            $table->timestamps();
            $table->index('shop_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_finance_reconciles');
    }
};