<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_production_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_no', 50)->unique();
            $table->date('plan_date');
            $table->string('status', 30)->default('draft');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['plan_no']);
            $table->index(['plan_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_production_plans');
    }
};
