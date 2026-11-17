<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_quality_inspection_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quality_inspection_id');
            $table->integer('line_number')->default(1);
            $table->string('inspection_item', 200);
            $table->string('specification', 200)->nullable();
            $table->string('result', 200)->nullable();
            $table->string('judgment', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->unique(['quality_inspection_id', 'line_number'], 'quality_inspection_lines_quality_inspection_id_line_num_eae4809c');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_quality_inspection_lines');
    }
};
