<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsWarnSetting extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_goods_warn_setting';

    protected $fillable = [
        'system_goods_id', 'min_stock_threshold', 'warn_enabled',
        'notify_users', 'last_warn_at',
    ];

    protected function casts(): array
    {
        return [
            'warn_enabled' => 'boolean',
            'last_warn_at' => 'datetime',
        ];
    }

    public function systemGoods(): BelongsTo
    {
        return $this->belongsTo(SystemGoods::class);
    }
}
