<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_production_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 50)->unique();
            $table->unsignedBigInteger('production_order_id');
            $table->date('receipt_date');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['receipt_no']);
            $table->index(['production_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_production_receipts');
    }
};
