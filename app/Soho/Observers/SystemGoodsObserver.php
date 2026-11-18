<?php

namespace App\Soho\Observers;

use App\Soho\Events\StockChanged;
use App\Soho\Models\SystemGoods;

class SystemGoodsObserver
{
    public function updated(SystemGoods $goods): void
    {
        if ($goods->isDirty('system_stock')) {
            event(new StockChanged(
                goods: $goods,
                oldStock: $goods->getOriginal('system_stock'),
                newStock: $goods->system_stock,
            ));
        }
    }

    public function created(SystemGoods $goods): void
    {
        // 新商品创建逻辑
    }

    public function deleted(SystemGoods $goods): void
    {
        // 商品删除后清理SKU映射
        $goods->platformSkus()->delete();
    }
}
