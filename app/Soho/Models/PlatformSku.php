<?php

namespace App\Soho\Models;

use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformSku extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use LogsActivity;
    protected $table = 'soho_platform_skus';

    protected $fillable = [
        'system_goods_id', 'shop_id', 'platform_sku', 'platform_goods_id',
        'platform_goods_url', 'platform_price', 'platform_stock',
        'stock_sync_enabled', 'sync_status', 'last_sync_at', 'sync_error',
    ];

    protected function casts(): array
    {
        return [
            'platform_price'     => 'decimal:2',
            'stock_sync_enabled' => 'boolean',
            'sync_status'        => 'integer',
            'last_sync_at'       => 'datetime',
        ];
    }

    public function systemGoods(): BelongsTo
    {
        return $this->belongsTo(SystemGoods::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
