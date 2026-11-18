<?php

namespace App\Soho\Events;

use App\Soho\Models\SystemGoods;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SystemGoods $goods,
        public int $oldStock,
        public int $newStock,
        public string $changeType = 'manual',
    ) {
    }
}
