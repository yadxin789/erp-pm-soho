<?php

namespace App\Soho\Models;

use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemGoods extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use SoftDeletes, LogsActivity;

    protected $table = 'soho_system_goods';

    protected $fillable = [
        'goods_name', 'system_sku', 'barcode', 'cost_price', 'selling_price',
        'system_stock', 'images', 'description', 'category', 'brand',
        'unit', 'status', 'min_stock_warn', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'    => 'decimal:2',
            'selling_price' => 'decimal:2',
            'images'        => 'array',
            'status'        => 'boolean',
        ];
    }

    public function platformSkus(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlatformSku::class);
    }

    public function stockSyncLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockSyncLog::class);
    }

    public function warnSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GoodsWarnSetting::class);
    }

    public function publishRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoodsPublishRecord::class);
    }

    public function isLowStock(): bool
    {
        return $this->system_stock <= $this->min_stock_warn && $this->min_stock_warn > 0;
    }

    /**
     * 访问器：商品名称（映射 goods_name）
     */
    public function getNameAttribute(): string
    {
        return $this->goods_name;
    }

    /**
     * 访问器：SKU编码（映射 system_sku）
     */
    public function getSkuAttribute(): string
    {
        return $this->system_sku;
    }
}
