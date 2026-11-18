<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->comment('货币代码');
            $table->string('name', 50)->comment('货币名称');
            $table->string('symbol', 10)->nullable()->comment('货币符号');
            $table->decimal('rate', 12, 6)->default(1)->comment('汇率');
            $table->tinyInteger('decimal_places')->default(2)->comment('小数位数');
            $table->boolean('is_default')->default(false)->comment('是否默认货币');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 0禁用');
            $table->timestamps();

            $table->unique('code');
            $table->index(['status', 'is_default'], 'ix_52_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_currencies');
    }
};
