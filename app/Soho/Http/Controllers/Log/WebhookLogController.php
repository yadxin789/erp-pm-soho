<?php

namespace App\Soho\Http\Controllers\Log;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Webhook 日志与事件重放
 *
 * 数据源：
 * - soho_webhook_event_logs：平台回调接收日志（status / error_message）
 * - soho_domain_events：领域事件（publish_status / retry_count，用于重放）
 * - soho_event_logs：模型事件流水
 */
class WebhookLogController extends Controller
{
    /** Webhook 接收日志 */
    public function index(Request $request)
    {
        $base = DB::table('soho_webhook_event_logs');

        $rows = (clone $base)
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform', $p))
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(function ($qq) use ($kw) {
                $qq->where('event_id', 'like', "%{$kw}%")->orWhere('event_type', 'like', "%{$kw}%");
            }))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'success' => (clone $base)->where('status', 'success')->count(),
            'processing' => (clone $base)->where('status', 'processing')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
        ];
        $platforms = (clone $base)->select('platform')->distinct()->pluck('platform');

        return view('soho.log.webhook', compact('rows', 'stats', 'platforms'));
    }

    /** Webhook 失败记录 */
    public function failures(Request $request)
    {
        $base = DB::table('soho_webhook_event_logs');

        $rows = (clone $base)
            ->where(function ($q) {
                $q->where('status', 'failed')->orWhereNotNull('error_message');
            })
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform', $p))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $failed = (clone $base)->where('status', 'failed')->count();
        $unprocessed = (clone $base)->whereNull('processed_at')->count();
        $stats = [
            'failed' => $failed,
            'unprocessed' => $unprocessed,
            'total' => (clone $base)->count(),
            'platforms_affected' => (clone $base)->where('status', 'failed')->select('platform')->distinct()->count(),
        ];

        // 失败事件类型分布
        $byType = (clone $base)
            ->where('status', 'failed')
            ->select('event_type')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('event_type')
            ->orderByDesc('cnt')
            ->get();

        $platforms = (clone $base)->select('platform')->distinct()->pluck('platform');

        return view('soho.log.webhook-failures', compact('rows', 'stats', 'byType', 'platforms'));
    }

    /** 事件重放（领域事件未发布 / 发布失败） */
    public function replay(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'event_ids' => ['required', 'array', 'min:1'],
                'event_ids.*' => ['integer'],
            ], [], ['event_ids' => '事件']);

            $affected = DB::table('soho_domain_events')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
                ->whereIn('id', $data['event_ids'])
                ->whereIn('publish_status', ['pending', 'failed'])
                ->update([
                    'publish_status' => 'pending',
                    'retry_count' => DB::raw('retry_count + 1'),
                    'published_at' => null,
                    'updated_at' => now(),
                ]);

            return redirect()->route('soho.event.replay')
                ->with('success', "已将 {$affected} 个事件重新入队等待发布（重放次数 +1）。");
        }

        $base = DB::table('soho_domain_events')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $pending = (clone $base)
            ->whereIn('publish_status', ['pending', 'failed'])
            ->when($request->input('aggregate_type'), fn ($q, $ty) => $q->where('aggregate_type', $ty))
            ->orderBy('occurred_at')
            ->limit(50)
            ->get(['id', 'event_type', 'aggregate_type', 'aggregate_id', 'publish_status', 'retry_count', 'occurred_at', 'published_at']);

        $stats = [
            'pending' => (clone $base)->where('publish_status', 'pending')->count(),
            'failed' => (clone $base)->where('publish_status', 'failed')->count(),
            'published' => (clone $base)->where('publish_status', 'published')->count(),
            'replayed' => (clone $base)->where('retry_count', '>', 0)->count(),
        ];

        $types = (clone $base)->select('aggregate_type')->distinct()->pluck('aggregate_type');

        $recentEventLogs = DB::table('soho_event_logs')
            ->when($request->input('model_type'), fn ($q, $mt) => $q->where('model_type', $mt))
            ->orderByDesc('id')
            ->limit(15)
            ->get(['id', 'event_type', 'model_type', 'model_id', 'user_id', 'processed_at', 'created_at']);

        return view('soho.event.replay', compact('pending', 'stats', 'types', 'recentEventLogs'));
    }

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }
}
