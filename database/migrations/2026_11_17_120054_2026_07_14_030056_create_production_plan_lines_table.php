<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_production_plan_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_plan_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->date('plan_date')->nullable();
            $table->unsignedBigInteger('work_center_id')->nullable();
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->unique(['production_plan_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_production_plan_lines');
    }
};
