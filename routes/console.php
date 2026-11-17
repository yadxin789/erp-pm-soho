<?php

/*
|--------------------------------------------------------------------------
| 调度合并（T0-05）
|--------------------------------------------------------------------------
| 23 → 实测 24 条：ERP 7 个自有 + 3 个框架清理（原在 ERP console.php）+
| PM 8 个 + SOHO 6 个。
|
| 与原项目差异（仅三处，其余 cron 表达式与互斥语义原样保留）：
|   1) 任务名统一加模块前缀（erp: / pm: / soho: / core:），防跨模块重名
|   2) 命令签名同步加前缀（见 T0-06）：db:backup→erp:db-backup、
|      files:gc→pm:files-gc、projects:refresh-status→pm:projects-refresh-status 等
|   3) 日志文件按模块分目录（logs/{module}/…），避免三模块写同一文件
|
| 未迁入：SOHO bootstrap/app.php 里与 console.php 重复的 3 条（订单/库存/Token），
| 以及 `inspire` 每日 01:00 的演示调度（框架默认命令保留，演示调度删除）。
| 原 SOHO 4 条 bootstrap 调度与 console.php 完全重复，属"双注册"，合并后去重。
|
| 运行前提同上：crontab（或 Windows 任务计划）每分钟执行 `php artisan schedule:run`；
| withoutOverlapping 依赖缓存锁，CACHE_STORE 不能是 array。
*/

use App\Soho\Jobs\RefreshPlatformTokenJob;
use App\Soho\Jobs\StockWarningNotificationJob;
use App\Soho\Jobs\SyncAllOrdersJob;
use App\Soho\Jobs\SyncAllStockJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* =====================================================================
 | ERP
 | ===================================================================== */

// 健康检查：每分钟自检，同时作为"调度是否存活"的心跳
Schedule::command('erp:health')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground()
    ->name('erp:health')
    ->appendOutputTo(storage_path('logs/erp/health.log'));

// 状态自动转换：每 5 秒扫描（报价过期/工单齐套/订单发货达标/采购收货达标/预占到期）
Schedule::command('erp:state-transition')
    ->everyFiveSeconds()
    ->withoutOverlapping(20)
    ->runInBackground()
    ->name('erp:state-transition');

// 数据库全量备份：业务低峰期
Schedule::command('erp:db-backup')
    ->dailyAt('02:00')
    ->withoutOverlapping(120)
    ->onFailure(function () {
        app(\App\Erp\Services\AlertService::class)->fire(
            'backup',
            'backup.schedule_failed',
            'critical',
            '定时备份任务执行失败',
            'erp:db-backup 在调度中执行失败，请查看 storage/logs/erp/scheduler.log 与备份台账。'
        );
    })
    ->name('erp:db-backup')
    ->appendOutputTo(storage_path('logs/erp/scheduler.log'));

// 过期数据轮转
Schedule::command('erp:prune')
    ->dailyAt('03:10')
    ->withoutOverlapping(60)
    ->name('erp:prune')
    ->appendOutputTo(storage_path('logs/erp/scheduler.log'));

// 低库存预警：每日上班前推送
Schedule::command('erp:low-stock-alert')
    ->dailyAt('08:00')
    ->withoutOverlapping(30)
    ->name('erp:low-stock-alert')
    ->appendOutputTo(storage_path('logs/erp/scheduler.log'));

// 物流轨迹轮询
Schedule::command('erp:logistics-track --limit=50 --interval=6')
    ->hourly()
    ->withoutOverlapping(55)
    ->runInBackground()
    ->name('erp:logistics-track')
    ->appendOutputTo(storage_path('logs/erp/scheduler.log'));

// 电商订单同步：Job 自带同平台 1 小时互斥锁
Schedule::command('erp:ecommerce-sync')
    ->everyThirtyMinutes()
    ->withoutOverlapping(25)
    ->name('erp:ecommerce-sync')
    ->appendOutputTo(storage_path('logs/erp/scheduler.log'));

/* =====================================================================
 | PM
 | ===================================================================== */

// 每天 0 点刷新项目/任务状态、进度与统计
Schedule::command('pm:projects-refresh-status')
    ->dailyAt('00:00')
    ->name('pm:projects-refresh-status')
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// 回收站清理（含物理文件与历史快照）+ 语音临时文件
Schedule::command('pm:trash-purge-expired')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->name('pm:trash-purge-expired')
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// 物理文件巡检：孤儿文件/空目录，双向核对数据库与磁盘
Schedule::command('pm:files-gc --fix --purge-dead')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->name('pm:files-gc')
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// 任务到期提醒（未来 3 天）
Schedule::call('App\Pm\Services\ReminderService@checkUpcomingDeadlines')
    ->name('pm:reminder-upcoming-deadlines')
    ->dailyAt('08:00')
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// 任务逾期检查
Schedule::call('App\Pm\Services\ReminderService@checkOverdueTasks')
    ->name('pm:reminder-overdue-tasks')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// 审核超时提醒
Schedule::call('App\Pm\Services\ReminderService@checkAuditOverdue')
    ->name('pm:reminder-audit-overdue')
    ->twiceDaily(10, 15)
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// 项目逾期检查
Schedule::call('App\Pm\Services\ReminderService@checkOverdueProjects')
    ->name('pm:reminder-overdue-projects')
    ->dailyAt('01:00')
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

// AI 变更提案过期作废
Schedule::call('App\Pm\Services\AiChangeProposalService@expireStale')
    ->name('pm:ai-expire-proposals')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/pm/scheduler.log'));

/* =====================================================================
 | SOHO
 | ===================================================================== */

Schedule::job(SyncAllOrdersJob::class, 'default')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->name('soho:order-sync-all')
    ->appendOutputTo(storage_path('logs/soho/scheduler-orders.log'));

Schedule::job(SyncAllStockJob::class, 'default')
    ->everyTenMinutes()
    ->withoutOverlapping(15)
    ->name('soho:stock-sync-all')
    ->appendOutputTo(storage_path('logs/soho/scheduler-stock.log'));

Schedule::job(RefreshPlatformTokenJob::class, 'default')
    ->everyTwoHours()
    ->withoutOverlapping(30)
    ->name('soho:token-refresh')
    ->appendOutputTo(storage_path('logs/soho/scheduler-token.log'));

Schedule::job(StockWarningNotificationJob::class, 'notifications')
    ->dailyAt('09:00')
    ->withoutOverlapping(60)
    ->name('soho:stock-warning-notification')
    ->appendOutputTo(storage_path('logs/soho/scheduler-stock-warning.log'));

Schedule::command('model:prune', ['--model' => 'App\\Soho\\Models\\PlatformApiLog'])
    ->daily()
    ->name('soho:cleanup-api-logs')
    ->appendOutputTo(storage_path('logs/soho/scheduler-cleanup.log'));

Schedule::command('queue:prune-failed', ['--hours' => 168])
    ->weekly()
    ->name('soho:cleanup-failed-jobs')
    ->appendOutputTo(storage_path('logs/soho/scheduler-cleanup.log'));

/* =====================================================================
 | Core / 框架级（原属 ERP console.php，合并后归入 core）
 | ===================================================================== */

Schedule::command('queue:prune-batches --hours=48')->daily()->name('core:queue-prune-batches');
Schedule::command('queue:prune-failed --hours=720')->daily()->name('core:queue-prune-failed');
Schedule::command('auth:clear-resets')->daily()->name('core:auth-clear-resets');
