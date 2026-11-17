<?php

namespace App\Soho\Console\Commands;

use App\Soho\Jobs\SyncAllStockJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * 原 SOHO 闭包命令 `sync:stock` → soho:sync-stock。
 */
class SyncStockCommand extends Command
{
    protected $signature = 'soho:sync-stock';
    protected $description = '手动触发全平台库存同步（派发队列任务）';

    public function handle(): int
    {
        $this->info('开始手动同步库存...');
        Bus::dispatch(new SyncAllStockJob());
        $this->info('库存同步任务已加入队列');

        return self::SUCCESS;
    }
}