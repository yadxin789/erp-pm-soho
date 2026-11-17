<?php

namespace App\Soho\Console\Commands;

use App\Soho\Jobs\SyncAllOrdersJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * 原 SOHO 闭包命令 `sync:orders` → soho:sync-orders。
 */
class SyncOrdersCommand extends Command
{
    protected $signature = 'soho:sync-orders';
    protected $description = '手动触发全平台订单同步（派发队列任务）';

    public function handle(): int
    {
        $this->info('开始手动同步订单...');
        Bus::dispatch(new SyncAllOrdersJob());
        $this->info('订单同步任务已加入队列');

        return self::SUCCESS;
    }
}