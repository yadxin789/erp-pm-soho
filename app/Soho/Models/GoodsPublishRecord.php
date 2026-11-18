<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsPublishRecord extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_goods_publish_records';

    protected $fillable = [
        'system_goods_id',
        'platform',
        'shop_id',
        'platform_goods_id',
        'status',
        'error_message',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * 获取关联的系统商品
     */
    public function goods(): BelongsTo
    {
        return $this->belongsTo(SystemGoods::class, 'system_goods_id');
    }

    /**
     * 获取发布的店铺
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
