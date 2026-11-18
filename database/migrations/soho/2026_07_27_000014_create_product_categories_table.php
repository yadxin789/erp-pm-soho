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
        Schema::create('soho_product_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned()->comment('租户ID');
            $table->bigInteger('parent_id')->unsigned()->nullable()->comment('父级类目ID');
            $table->string('name', 100)->comment('类目名称');
            $table->string('slug', 100)->nullable()->comment('别名');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();

            $table->index(['tenant_id', 'parent_id'], 'ix_35_1');
            $table->index(['tenant_id', 'is_active'], 'ix_35_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_product_categories');
    }
};