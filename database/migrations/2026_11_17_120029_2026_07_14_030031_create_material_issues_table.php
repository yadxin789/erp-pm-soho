<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_material_issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no', 50)->unique();
            $table->string('issue_type', 30)->default('production');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->unsignedBigInteger('work_center_id')->nullable();
            $table->date('issue_date');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['issue_no']);
            $table->index(['production_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_material_issues');
    }
};
