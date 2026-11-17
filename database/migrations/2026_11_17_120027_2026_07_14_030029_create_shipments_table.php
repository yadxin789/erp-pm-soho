<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no', 50)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->date('shipment_date');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['shipment_no']);
            $table->index(['customer_id', 'status']);
            $table->index(['sales_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_shipments');
    }
};
