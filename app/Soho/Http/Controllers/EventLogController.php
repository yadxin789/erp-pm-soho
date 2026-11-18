<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventLogController extends Controller
{
    /**
     * 事件日志列表
     * 支持按事件类型、模型类型、处理状态、时间范围进行筛选
     */
    public function index(Request $request)
    {
        $eventLogs = EventLog::with(['user'])
            ->when($request->search, function ($query) use ($request) {
                $query->where('event_type', 'like', "%{$request->search}%")
                    ->orWhere('model_type', 'like', "%{$request->search}%");
            })
            ->when($request->event_type, function ($query) use ($request) {
                $query->where('event_type', $request->event_type);
            })
            ->when($request->model_type, function ($query) use ($request) {
                $query->where('model_type', $request->model_type);
            })
            ->when($request->status === 'processed', function ($query) {
                $query->whereNotNull('processed_at');
            })
            ->when($request->status === 'unprocessed', function ($query) {
                $query->whereNull('processed_at');
            })
            ->when($request->date_start, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // 获取统计数据
        $stats = [
            'total' => EventLog::count(),
            'processed' => EventLog::whereNotNull('processed_at')->count(),
            'unprocessed' => EventLog::whereNull('processed_at')->count(),
            'today' => EventLog::whereDate('created_at', today())->count(),
        ];

        // 获取事件类型统计
        $eventTypeStats = EventLog::select('event_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'event_type')
            ->toArray();

        return view('event-logs.index', compact('eventLogs', 'stats', 'eventTypeStats'));
    }

    /**
     * 事件日志详情
     * 展示事件的完整信息，包括载荷数据和处理状态
     */
    public function show(EventLog $eventLog)
    {
        $eventLog->load(['user']);

        // 获取上一条和下一条日志用于导航
        $prevEvent = EventLog::where('id', '<', $eventLog->id)->orderByDesc('id')->first();
        $nextEvent = EventLog::where('id', '>', $eventLog->id)->orderBy('id')->first();

        return view('event-logs.show', compact('eventLog', 'prevEvent', 'nextEvent'));
    }

    /**
     * 重新处理事件
     * 对于处理失败或未处理的事件，手动触发重新处理
     */
    public function reprocess(EventLog $eventLog)
    {
        // 记录处理开始时间
        $eventLog->update([
            'processed_at' => now(),
        ]);

        try {
            // 根据事件类型分发到不同的处理逻辑
            $this->dispatchEventProcessing($eventLog);

            return response()->json([
                'success' => true,
                'message' => '事件已重新处理',
                'data' => [
                    'id' => $eventLog->id,
                    'event_type' => $eventLog->event_type,
                    'processed_at' => $eventLog->processed_at,
                ],
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            Log::error('事件重新处理失败', [
                'event_log_id' => $eventLog->id,
                'event_type' => $eventLog->event_type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '事件处理失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 根据事件类型分发处理逻辑
     * 支持订单状态变更、发货创建、库存变更等事件
     */
    protected function dispatchEventProcessing(EventLog $eventLog): void
    {
        $payload = $eventLog->payload_json ?? [];

        switch ($eventLog->event_type) {
            case 'order_status_changed':
                // 订单状态变更处理
                if ($eventLog->model_type === 'PlatformOrder' && $eventLog->model_id) {
                    $order = $eventLog->model_type::find($eventLog->model_id);
                    if ($order) {
                        Log::info('重新处理订单状态变更事件', [
                            'order_id' => $order->id,
                            'event_log_id' => $eventLog->id,
                            'payload' => $payload,
                        ]);
                    }
                }
                break;

            case 'shipment_created':
                // 发货创建事件处理
                Log::info('重新处理发货创建事件', [
                    'event_log_id' => $eventLog->id,
                    'payload' => $payload,
                ]);
                break;

            case 'stock_changed':
                // 库存变更事件处理
                Log::info('重新处理库存变更事件', [
                    'event_log_id' => $eventLog->id,
                    'payload' => $payload,
                ]);
                break;

            default:
                // 其他事件类型记录日志即可
                Log::info('重新处理事件', [
                    'event_log_id' => $eventLog->id,
                    'event_type' => $eventLog->event_type,
                    'payload' => $payload,
                ]);
                break;
        }
    }
}