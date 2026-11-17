<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 补齐模型中已声明、但表结构尚未创建的字段：
     * - 单据表补 deleted_at（模型使用了 SoftDeletes）
     * - number_sequences 补年度/月度重置记录字段
     * - production_orders 补所属生产计划与来源销售订单
     */
    public function up(): void
    {
        $softDeleteTables = ['erp_material_issues', 'erp_production_receipts', 'erp_receipts', 'erp_shipments'];
        foreach ($softDeleteTables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }

        if (Schema::hasTable('erp_number_sequences')) {
            Schema::table('erp_number_sequences', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_number_sequences', 'last_reset_year')) {
                    $table->integer('last_reset_year')->nullable();
                }
                if (!Schema::hasColumn('erp_number_sequences', 'last_reset_month')) {
                    $table->integer('last_reset_month')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_production_orders')) {
            Schema::table('erp_production_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_production_orders', 'production_plan_id')) {
                    $table->unsignedBigInteger('production_plan_id')->nullable()->after('item_id');
                }
                if (!Schema::hasColumn('erp_production_orders', 'sales_order_id')) {
                    $table->unsignedBigInteger('sales_order_id')->nullable()->after('production_plan_id');
                }
            });

            Schema::table('erp_production_orders', function (Blueprint $table) {
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('erp_production_orders') && Schema::hasColumn('erp_production_orders', 'production_plan_id')) {
            Schema::table('erp_production_orders', function (Blueprint $table) {
                $table->dropForeign(['production_plan_id']);
                $table->dropForeign(['sales_order_id']);
                $table->dropColumn(['production_plan_id', 'sales_order_id']);
            });
        }

        if (Schema::hasTable('erp_number_sequences')) {
            Schema::table('erp_number_sequences', function (Blueprint $table) {
                $table->dropColumn(['last_reset_year', 'last_reset_month']);
            });
        }

        foreach (['erp_material_issues', 'erp_production_receipts', 'erp_receipts', 'erp_shipments'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
