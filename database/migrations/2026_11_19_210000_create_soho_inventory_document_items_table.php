<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-3：库存单据执行闭环所需的表结构补充
 * 1. soho_inventory_documents 增加调拨目标仓 target_warehouse_id
 * 2. 新增 soho_inventory_document_items（单据明细行，执行库存变动的来源）
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('soho_inventory_documents') && ! Schema::hasColumn('soho_inventory_documents', 'target_warehouse_id')) {
            Schema::table('soho_inventory_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('target_warehouse_id')->nullable()->after('warehouse_id');
            });
        }

        if (! Schema::hasTable('soho_inventory_document_items')) {
            Schema::create('soho_inventory_document_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('document_id')->index();
                $table->unsignedBigInteger('sku_id')->nullable()->index();
                $table->string('product_name')->nullable();
                $table->decimal('quantity', 14, 2)->default(0);
                $table->decimal('unit_cost', 14, 4)->default(0);
                $table->string('batch_no')->nullable();
                $table->string('remark')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_inventory_document_items');
        if (Schema::hasTable('soho_inventory_documents') && Schema::hasColumn('soho_inventory_documents', 'target_warehouse_id')) {
            Schema::table('soho_inventory_documents', function (Blueprint $table) {
                $table->dropColumn('target_warehouse_id');
            });
        }
    }
};
