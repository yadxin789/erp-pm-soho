<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_inventory_flows', function (Blueprint $table) {
            $table->id();
            $table->string('flow_no', 50)->unique('ux_2_1');
            $table->enum('type', ['in', 'out', 'transfer', 'stocktake', 'sync']);
            $table->foreignId('warehouse_id');
            $table->foreignId('sku_id');
            $table->integer('quantity');
            $table->integer('before_quantity')->default(0);
            $table->integer('after_quantity')->default(0);
            $table->string('reference_no', 100)->nullable();
            $table->foreignId('operator_id');
            $table->string('remark', 500)->nullable();
            $table->timestamps();
            $table->index('type');
            $table->index('warehouse_id');
            $table->index('sku_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_inventory_flows');
    }
};