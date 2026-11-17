<?php

namespace App\Soho\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 原 SOHO routes/console.php 中的闭包命令 `queue:health-check`，
 * T0-06 迁为类命令并加 soho: 前缀。
 */
class QueueHealthCheckCommand extends Command
{
    protected $signature = 'soho:queue-health-check';
    protected $description = '检查队列健康状态（待处理/失败任务数量）';

    public function handle(): int
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();

        $this->info("待处理任务: {$pending}");
        $this->info("失败任务: {$failed}");

        if ($pending > 100) {
            $this->warn('警告：待处理任务过多，请检查队列消费者状态');
        }
        if ($failed > 10) {
            $this->error('警告：失败任务过多，请检查任务执行情况');
        }

        return self::SUCCESS;
    }
}