<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_routing_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('routing_header_id');
            $table->integer('operation_number')->default(1);
            $table->string('operation_code', 50);
            $table->string('operation_name', 100);
            $table->unsignedBigInteger('work_center_id')->nullable();
            $table->unsignedBigInteger('production_line_id')->nullable();
            $table->decimal('standard_time', 10, 2)->default(0);
            $table->decimal('setup_time', 10, 2)->default(0);
            $table->boolean('require_report')->default(false);
            $table->boolean('require_inspection')->default(false);
            $table->boolean('is_subcontract')->default(false);
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->unique(['routing_header_id', 'operation_number'], 'ix_25bf4385957e05b0442a');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_routing_operations');
    }
};
