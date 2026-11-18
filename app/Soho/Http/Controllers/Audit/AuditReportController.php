<?php

namespace App\Soho\Http\Controllers\Audit;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 审计报表控制器
 * 汇总展示各类审计统计（操作日志数、敏感操作数、异常事件数），
 * 支持按时间范围筛选，并提供按日期维度的明细统计
 */
class AuditReportController extends Controller
{
    /**
     * 审计报表首页
     * 汇总当前租户下的操作日志、敏感操作、异常事件统计，按时间范围筛选并分页展示每日明细
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 解析时间范围筛选参数，默认为最近30天
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $start = $dateFrom . ' 00:00:00';
        $end = $dateTo . ' 23:59:59';

        // 汇总统计：操作日志数（基于 operation_logs 表）
        $operationCount = DB::table('soho_operation_logs')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // 汇总统计：敏感操作数（基于 sensitive_access_logs 表）
        $sensitiveCount = DB::table('soho_sensitive_access_logs')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('accessed_at', [$start, $end])
            ->count();

        // 汇总统计：异常事件数（基于 exception_cases 表）
        $exceptionCount = DB::table('soho_exception_cases')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // 统计卡片数据
        $stats = [
            'operation' => $operationCount,
            'sensitive' => $sensitiveCount,
            'exception' => $exceptionCount,
            'total'     => $operationCount + $sensitiveCount + $exceptionCount,
        ];

        // 按日期维度聚合每日审计统计：分别查询三类日志的每日计数，再在 PHP 中按日期合并
        // 操作日志按日期分组
        $operationDaily = DB::table('soho_operation_logs')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE(created_at) as stat_date, COUNT(*) as cnt")
            ->groupBy('stat_date')
            ->pluck('cnt', 'stat_date');

        // 敏感操作按日期分组
        $sensitiveDaily = DB::table('soho_sensitive_access_logs')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('accessed_at', [$start, $end])
            ->selectRaw("DATE(accessed_at) as stat_date, COUNT(*) as cnt")
            ->groupBy('stat_date')
            ->pluck('cnt', 'stat_date');

        // 异常事件按日期分组
        $exceptionDaily = DB::table('soho_exception_cases')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE(created_at) as stat_date, COUNT(*) as cnt")
            ->groupBy('stat_date')
            ->pluck('cnt', 'stat_date');

        // 合并所有出现过的日期，构造每日统计明细
        $allDates = $operationDaily->keys()
            ->merge($sensitiveDaily->keys())
            ->merge($exceptionDaily->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $merged = $allDates->map(function ($date) use ($operationDaily, $sensitiveDaily, $exceptionDaily) {
            return [
                'stat_date'        => $date,
                'operation_count'  => $operationDaily->get($date, 0),
                'sensitive_count'  => $sensitiveDaily->get($date, 0),
                'exception_count'  => $exceptionDaily->get($date, 0),
                'daily_total'      => $operationDaily->get($date, 0)
                    + $sensitiveDaily->get($date, 0)
                    + $exceptionDaily->get($date, 0),
            ];
        });

        // 手动分页，每页20条
        $page = $request->input('page', 1);
        $perPage = 20;
        $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();
        $dailyStats = new LengthAwarePaginator($items, $merged->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // 时间范围预设选项
        $rangeOptions = [
            '7'   => '最近7天',
            '30'  => '最近30天',
            '90'  => '最近90天',
            '365' => '最近一年',
        ];

        return view('audit.reports.index', compact('dailyStats', 'stats', 'rangeOptions', 'dateFrom', 'dateTo'));
    }
}
