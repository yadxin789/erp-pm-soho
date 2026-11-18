<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_user_shop_limit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('员工ID');
            $table->foreignId('shop_id')->comment('店铺ID');
            $table->timestamps();

            $table->unique(['user_id', 'shop_id'], 'ux_7_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_user_shop_limit');
    }
};
