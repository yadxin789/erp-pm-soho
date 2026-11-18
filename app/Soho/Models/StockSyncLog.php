<?php

namespace App\Soho\Models;

use App\Soho\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockSyncLog extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    use LogsActivity;
    protected $table = 'soho_stock_sync_logs';

    protected $fillable = [
        'system_goods_id', 'shop_id', 'platform', 'before_stock', 'after_stock',
        'sync_type', 'sync_status', 'error_message', 'operator_id',
    ];

    public function systemGoods(): BelongsTo
    {
        return $this->belongsTo(SystemGoods::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function getSyncTypeLabelAttribute(): string
    {
        return match($this->sync_type) {
            'push'  => '主动推送',
            'pull'  => '被动拉取',
            'manual'=> '手动同步',
            default => $this->sync_type,
        };
    }
}
