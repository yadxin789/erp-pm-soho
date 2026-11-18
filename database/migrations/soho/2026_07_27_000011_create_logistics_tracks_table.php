<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_logistics_tracks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('package_id')->unsigned()->comment('包裹ID');
            $table->string('tracking_number', 100)->comment('运单号');
            $table->string('original_status', 100)->nullable()->comment('原始物流状态');
            $table->string('unified_status', 40)->nullable()->comment('标准化状态');
            $table->string('location', 200)->nullable()->comment('轨迹地点');
            $table->text('description')->nullable()->comment('轨迹描述');
            $table->timestamp('track_time')->comment('轨迹时间');
            $table->string('source', 30)->nullable()->comment('数据来源');
            $table->json('raw_data')->nullable()->comment('原始数据');
            $table->timestamps();

            $table->index(['tracking_number', 'track_time'], 'ix_32_1');
            $table->index(['package_id'], 'ix_32_2');
            $table->index(['tenant_id', 'tracking_number'], 'ix_32_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_logistics_tracks');
    }
};