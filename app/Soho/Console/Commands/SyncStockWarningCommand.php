<?php

namespace App\Soho\Console\Commands;

use App\Soho\Jobs\StockWarningNotificationJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * 原 SOHO 闭包命令 `sync:stock-warning` → soho:sync-stock-warning。
 */
class SyncStockWarningCommand extends Command
{
    protected $signature = 'soho:sync-stock-warning';
    protected $description = '手动触发库存预警检测（派发队列任务）';

    public function handle(): int
    {
        $this->info('开始库存预警检测...');
        Bus::dispatch(new StockWarningNotificationJob());
        $this->info('库存预警任务已加入队列');

        return self::SUCCESS;
    }
}