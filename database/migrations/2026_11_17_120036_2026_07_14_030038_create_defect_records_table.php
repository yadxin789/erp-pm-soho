<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_defect_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_no', 50)->unique();
            $table->string('defect_type', 30);
            $table->unsignedBigInteger('defect_code_id')->nullable();
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->string('disposition', 30)->default('pending');
            $table->text('description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->index(['record_no']);
            $table->index(['defect_type', 'disposition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_defect_records');
    }
};
