<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('received_quantity', 18, 4)->default(0);
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->decimal('tax_rate', 10, 2)->default(0);
            $table->decimal('line_amount', 18, 4)->default(0);
            $table->decimal('line_tax', 18, 4)->default(0);
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->unique(['purchase_order_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_purchase_order_lines');
    }
};
