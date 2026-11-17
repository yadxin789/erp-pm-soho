<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 客户发票（应收）
        Schema::create('erp_customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique();
            $table->foreignId('customer_id');
            $table->foreignId('sales_order_id')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->decimal('paid_amount', 18, 4)->default(0);
            $table->string('status', 20)->default('unpaid'); // unpaid/partial/paid/overdue/cancelled
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 供应商账单（应付）
        Schema::create('erp_supplier_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no', 50)->unique();
            $table->foreignId('supplier_id');
            $table->foreignId('purchase_order_id')->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->decimal('paid_amount', 18, 4)->default(0);
            $table->string('status', 20)->default('unpaid'); // unpaid/partial/paid/overdue/cancelled
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 收付款记录
        Schema::create('erp_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 50)->unique();
            $table->string('type', 20); // receipt(收款) / payment(付款)
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('supplier_id')->nullable();
            $table->foreignId('customer_invoice_id')->nullable();
            $table->foreignId('supplier_bill_id')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 18, 4);
            $table->string('method', 30)->default('bank_transfer'); // cash/bank_transfer/check/alipay/wechat
            $table->string('reference_no')->nullable(); // 银行流水号/支票号
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_payments');
        Schema::dropIfExists('erp_supplier_bills');
        Schema::dropIfExists('erp_customer_invoices');
    }
};
