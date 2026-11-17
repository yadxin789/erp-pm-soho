<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quote_no', 50)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->date('quote_date');
            $table->date('valid_until')->nullable();
            $table->string('status', 30)->default('draft');
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->decimal('total_tax', 18, 4)->default(0);
            $table->decimal('total_net_amount', 18, 4)->default(0);
            $table->string('currency', 10)->default('CNY');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['quote_no']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_quotations');
    }
};
