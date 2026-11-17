<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('batch_number', 50)->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->unsignedBigInteger('source_document_id');
            $table->string('source_document_type', 50);
            $table->string('source_document_no', 50);
            $table->date('reservation_date');
            $table->date('expiry_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->index(['source_document_type', 'source_document_id'], 'inventory_reservations_source_document_type_source_docu_8ea5f1ff');
            $table->index(['item_id', 'warehouse_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_inventory_reservations');
    }
};
