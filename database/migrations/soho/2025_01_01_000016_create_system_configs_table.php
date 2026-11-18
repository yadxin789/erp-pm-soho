<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_system_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique('ux_19_1')->comment('配置键');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('group', 50)->default('system')->comment('配置分组: system系统/business业务/notification通知');
            $table->string('type', 30)->default('string')->comment('值类型: string/int/bool/json');
            $table->string('label', 200)->nullable()->comment('配置说明');
            $table->string('remark', 500)->nullable()->comment('备注');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_system_configs');
    }
};
