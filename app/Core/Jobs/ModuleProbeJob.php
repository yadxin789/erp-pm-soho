<?php

namespace App\Core\Jobs;

use App\Core\Support\ModuleContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * 队列与模块上下文探针（T0-07 验证用，生产可保留为健康探针）
 *
 * 验证两件事：
 *  1) database 队列驱动可正常入队/消费（本机无 redis 扩展）
 *  2) 队列任务中 ModuleContext 必须显式携带，不能依赖 HTTP 上下文
 */
class ModuleProbeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $module = 'core',
        public string $note = 'probe',
    ) {
        // 任务入队即绑定模块，消费端用 ModuleContext::run 还原
        $this->onQueue($module);
    }

    public function handle(): void
    {
        $outside = ModuleContext::current();

        $result = ModuleContext::run($this->module, function () {
            return [
                'module'   => ModuleContext::id(),
                'scoped'   => ModuleContext::hasScope(),
                'queue'    => $this->queue ?? null,
                'probe_id' => $this->note,
            ];
        });

        Log::channel('single')->info('[ModuleProbeJob] 消费成功', [
            'context_before' => $outside,
            'context_inside' => $result,
            'restored'       => ModuleContext::current(),
        ]);
    }
}
