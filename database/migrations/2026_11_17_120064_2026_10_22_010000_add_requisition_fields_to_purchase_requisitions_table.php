<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_purchase_requisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('erp_purchase_requisitions', 'requisition_date')) {
                $table->date('requisition_date')->nullable()->after('req_no');
            }
            if (! Schema::hasColumn('erp_purchase_requisitions', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('requisition_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('erp_purchase_requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('erp_purchase_requisitions', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
            if (Schema::hasColumn('erp_purchase_requisitions', 'requisition_date')) {
                $table->dropColumn('requisition_date');
            }
        });
    }
};
