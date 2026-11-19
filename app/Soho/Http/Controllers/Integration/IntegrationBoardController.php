<?php

namespace App\Soho\Http\Controllers\Integration;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 集成与同步监控（连接状态 / 平台接口健康 / 限流 / 同步失败 / 同步重试）
 *
 * 数据源：
 * - soho_shop_sync_cursors：店铺各同步类型的游标与健康状态
 * - soho_platform_api_logs：平台接口调用日志
 * - soho_sync_tasks / soho_sync_task_logs：同步任务与任务日志
 */
class IntegrationBoardController extends Controller
{
    /** 连接状态（店铺 × 同步类型） */
    public function connectionStatus(Request $request)
    {
        $base = DB::table('soho_shop_sync_cursors')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $rows = (clone $base)
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform_code', $p))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('sync_type'), fn ($q, $ty) => $q->where('sync_type', $ty))
            ->orderByRaw("FIELD(status,'error','syncing','idle','disabled')")
            ->orderBy('shop_id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'syncing' => (clone $base)->where('status', 'syncing')->count(),
            'error' => (clone $base)->where('status', 'error')->count(),
            'disabled' => (clone $base)->where('is_enabled', 0)->count(),
        ];

        $shops = DB::table('soho_shops')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'shop_name', 'platform'])
            ->keyBy('id');
        $platforms = (clone $base)->select('platform_code')->distinct()->pluck('platform_code');
        $syncTypes = (clone $base)->select('sync_type')->distinct()->pluck('sync_type');

        return view('soho.integration.connection-status', compact('rows', 'stats', 'shops', 'platforms', 'syncTypes'));
    }

    /** 平台接口健康 */
    public function apiHealth(Request $request)
    {
        $base = DB::table('soho_platform_api_logs');
        $since = now()->subDay();

        $perPlatform = (clone $base)
            ->select('platform')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN response_status = 'success' THEN 1 ELSE 0 END) as success")
            ->selectRaw("SUM(CASE WHEN response_status <> 'success' THEN 1 ELSE 0 END) as failure")
            ->selectRaw('ROUND(AVG(response_time_ms)) as avg_ms')
            ->selectRaw('MAX(response_time_ms) as max_ms')
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();

        $platformFilter = $request->input('platform');
        $failures = (clone $base)
            ->when($platformFilter, fn ($q, $p) => $q->where('platform', $p))
            ->where('response_status', '<>', 'success')
            ->orderByDesc('id')
            ->limit(30)
            ->get(['id', 'platform', 'shop_id', 'api_method', 'response_status', 'error_message', 'response_time_ms', 'trace_id', 'created_at']);

        $tokens = DB::table('soho_token_refresh_logs')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->select('platform', 'result')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('platform', 'result')
            ->get();

        $stats = [
            'calls' => (clone $base)->count(),
            'calls_24h' => (clone $base)->where('created_at', '>=', $since)->count(),
            'failure_24h' => (clone $base)->where('created_at', '>=', $since)->where('response_status', '<>', 'success')->count(),
            'avg_ms' => (int) round((clone $base)->avg('response_time_ms') ?: 0),
        ];

        return view('soho.integration.api-health', compact('perPlatform', 'failures', 'tokens', 'stats', 'platformFilter'));
    }

    /** 平台接口限流监控 */
    public function rateLimit(Request $request)
    {
        $base = DB::table('soho_platform_api_logs');

        // 按平台统计当日调用量与峰值（分钟级）
        $today = now()->toDateString();
        $perPlatform = (clone $base)
            ->where('created_at', '>=', $today.' 00:00:00')
            ->select('platform')
            ->selectRaw('COUNT(*) as calls')
            ->selectRaw("SUM(CASE WHEN response_status = 'success' THEN 1 ELSE 0 END) as success")
            ->groupBy('platform')
            ->orderByDesc('calls')
            ->get();

        $peaks = (clone $base)
            ->select('platform')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as minute")
            ->selectRaw('COUNT(*) as calls')
            ->groupBy('platform', 'minute')
            ->orderByDesc('calls')
            ->limit(15)
            ->get();

        // 阈值配置：优先读 sync_cursors.config，缺省按平台默认 20 次/分钟
        $limits = [];
        $cursors = DB::table('soho_shop_sync_cursors')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['platform_code', 'config', 'batch_size', 'sync_interval']);
        foreach ($cursors as $c) {
            $cfg = $c->config ? json_decode($c->config, true) : [];
            $limits[$c->platform_code] = [
                'per_minute' => (int) ($cfg['rate_limit_per_minute'] ?? 20),
                'batch_size' => (int) $c->batch_size,
                'interval' => (int) $c->sync_interval,
            ];
        }

        $stats = [
            'platforms' => $perPlatform->count(),
            'today_calls' => $perPlatform->sum('calls'),
            'peak' => $peaks->max('calls') ?: 0,
            'over_limit' => 0,
        ];
        foreach ($perPlatform as $p) {
            $limit = $limits[$p->platform]['per_minute'] ?? 20;
            if ($p->calls > $limit * 60) {
                $stats['over_limit']++;
            }
        }

        return view('soho.integration.rate-limit', compact('perPlatform', 'peaks', 'limits', 'stats'));
    }

    /** 同步失败（失败任务 + 有失败明细的任务日志） */
    public function syncFailed(Request $request)
    {
        $tasks = DB::table('soho_sync_tasks')
            ->when($request->input('type'), fn ($q, $ty) => $q->where('type', $ty))
            ->when($request->input('shop_id'), fn ($q, $id) => $q->where('shop_id', $id))
            ->where(function ($q) {
                $q->where('status', 'failed')->orWhereNotNull('error_message');
            })
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'task_page')
            ->withQueryString();

        $logs = DB::table('soho_sync_task_logs')
            ->where(function ($q) {
                $q->where('status', 'failed')->orWhere('fail_count', '>', 0);
            })
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $stats = [
            'failed_tasks' => DB::table('soho_sync_tasks')->where('status', 'failed')->count(),
            'failed_logs' => DB::table('soho_sync_task_logs')->where('fail_count', '>', 0)->count(),
            'failed_records' => (int) DB::table('soho_sync_task_logs')->sum('fail_count'),
            'pending_tasks' => DB::table('soho_sync_tasks')->where('status', 'pending')->count(),
        ];

        $shops = DB::table('soho_shops')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'shop_name', 'platform'])
            ->keyBy('id');
        $types = DB::table('soho_sync_tasks')->select('type')->distinct()->pluck('type');

        return view('soho.sync.failed', compact('tasks', 'logs', 'stats', 'shops', 'types'));
    }

    /** 同步重试（把失败任务重置为待执行） */
    public function syncRetry(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'task_ids' => ['required', 'array', 'min:1'],
                'task_ids.*' => ['integer'],
            ], [], ['task_ids' => '任务']);

            $affected = DB::table('soho_sync_tasks')
                ->whereIn('id', $data['task_ids'])
                ->where('status', 'failed')
                ->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'result' => null,
                    'started_at' => null,
                    'completed_at' => null,
                    'updated_at' => now(),
                ]);

            return redirect()->route('soho.sync-task.retry')
                ->with('success', "已重置 {$affected} 个同步任务为待执行，将由调度器重新拉起。");
        }

        $failed = DB::table('soho_sync_tasks')
            ->where('status', 'failed')
            ->orderByDesc('id')
            ->get(['id', 'shop_id', 'type', 'status', 'error_message', 'started_at', 'completed_at', 'created_at']);

        $history = DB::table('soho_sync_tasks')
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'shop_id', 'type', 'status', 'created_at', 'updated_at']);

        $stats = [
            'failed' => $failed->count(),
            'pending' => $history->count(),
            'retryable' => $failed->count(),
            'completed_today' => DB::table('soho_sync_tasks')
                ->where('status', 'completed')
                ->where('completed_at', '>=', now()->toDateString().' 00:00:00')
                ->count(),
        ];

        $shops = DB::table('soho_shops')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'shop_name', 'platform'])
            ->keyBy('id');

        return view('soho.sync.retry', compact('failed', 'history', 'stats', 'shops'));
    }

    // ---------------------------------------------------------------- 内部方法

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }
}
