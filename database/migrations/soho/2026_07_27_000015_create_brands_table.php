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
        Schema::create('soho_brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->string('name', 100)->comment('品牌名称');
            $table->string('code', 50)->comment('品牌编码');
            $table->text('logo')->nullable()->comment('品牌Logo');
            $table->text('description')->nullable()->comment('品牌描述');
            $table->string('status', 20)->default('active')->comment('状态');
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'ux_36_1');
            $table->index(['tenant_id', 'status'], 'ix_36_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_brands');
    }
};