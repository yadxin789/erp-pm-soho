<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_no', 50)->unique('ux_1_1');
            $table->foreignId('order_id');
            $table->foreignId('express_company_id');
            $table->string('tracking_number', 200)->unique('ux_1_2');
            $table->decimal('weight', 10, 2)->nullable();
            $table->enum('status', ['pending', 'shipped', 'delivered', 'returned'])->default('pending');
            $table->timestamp('ship_time')->nullable();
            $table->foreignId('operator_id');
            $table->string('remark', 500)->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_packages');
    }
};