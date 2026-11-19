<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P3：平台 SKU 在「单店」维度唯一，防止演示/同步数据把同一平台 SKU 复用到多个商品。
 * 数据侧已由 php artisan p3:fix-uniqueness --apply 去重（保留首行，其余加 -NN 后缀）。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('soho_platform_skus')) {
            return;
        }
        Schema::table('soho_platform_skus', function (Blueprint $table) {
            $table->unique(['shop_id', 'platform_sku'], 'ux_platform_sku_shop');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('soho_platform_skus')) {
            return;
        }
        Schema::table('soho_platform_skus', function (Blueprint $table) {
            $table->dropUnique('ux_platform_sku_shop');
        });
    }
};
