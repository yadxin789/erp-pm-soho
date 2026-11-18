<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\SystemAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 系统告警控制器
 * 管理系统运行过程中产生的告警信息
 * 支持确认、解决、忽略告警操作
 */
class SystemAlertController extends Controller
{
    /**
     * 显示告警列表
     */
    public function index(Request $request)
    {
        $query = SystemAlert::query();

        // 关键词搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // 级别筛选
        if ($level = $request->input('level')) {
            $query->ofLevel($level);
        }

        // 来源筛选
        if ($source = $request->input('source')) {
            $query->ofSource($source);
        }

        // 状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $alerts = $query->orderByDesc('triggered_at')->paginate(15)->withQueryString();

        $levels = SystemAlert::getLevelLabels();
        $sources = SystemAlert::getSourceLabels();
        $statuses = SystemAlert::getStatusLabels();

        // 统计数据
        $stats = [
            'pending' => SystemAlert::pending()->count(),
            'critical' => SystemAlert::ofLevel('critical')->where('status', '!=', 'resolved')->count(),
            'today' => SystemAlert::whereDate('triggered_at', today())->count(),
            'resolved_today' => SystemAlert::whereDate('resolved_at', today())->count(),
        ];

        return view('system.alerts.index', compact('alerts', 'levels', 'sources', 'statuses', 'stats'));
    }

    /**
     * 显示告警详情
     */
    public function show(SystemAlert $alert)
    {
        $alert->load(['acknowledger', 'resolver']);

        return view('system.alerts.show', compact('alert'));
    }

    /**
     * 确认告警
     */
    public function acknowledge(SystemAlert $alert)
    {
        if ($alert->status !== 'pending') {
            return back()->with('error', '只能确认待处理的告警');
        }

        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_by' => Auth::id(),
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', "告警「{$alert->title}」已确认");
    }

    /**
     * 解决告警
     */
    public function resolve(Request $request, SystemAlert $alert)
    {
        $validated = $request->validate([
            'resolution_note' => 'required|string|max:1000',
        ]);

        $alert->update([
            'status' => 'resolved',
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'resolution_note' => $validated['resolution_note'],
        ]);

        return back()->with('success', "告警「{$alert->title}」已解决");
    }

    /**
     * 忽略告警
     */
    public function ignore(Request $request, SystemAlert $alert)
    {
        $validated = $request->validate([
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        $alert->update([
            'status' => 'ignored',
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'resolution_note' => $validated['resolution_note'] ?? '已忽略',
        ]);

        return back()->with('success', "告警「{$alert->title}」已忽略");
    }

    /**
     * 批量确认
     */
    public function batchAcknowledge(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:soho_system_alerts,id',
        ]);

        $count = SystemAlert::whereIn('id', $validated['ids'])
            ->where('status', 'pending')
            ->update([
                'status' => 'acknowledged',
                'acknowledged_by' => Auth::id(),
                'acknowledged_at' => now(),
            ]);

        return back()->with('success', "已批量确认 {$count} 条告警");
    }

    /**
     * 删除告警
     */
    public function destroy(SystemAlert $alert)
    {
        $alert->delete();

        return redirect()
            ->route('soho.system.alerts.index')
            ->with('success', '告警已删除');
    }
}
