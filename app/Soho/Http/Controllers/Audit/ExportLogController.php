<?php

namespace App\Soho\Http\Controllers\Audit;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 导出日志审计控制器
 * 负责追踪系统内数据导出行为，支持按导出模块、状态、操作人筛选
 */
class ExportLogController extends Controller
{
    /**
     * 导出日志列表
     * 展示当前租户下的数据导出记录，支持按导出单号、模块、状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询导出日志，限定当前租户
        $query = DB::table('soho_audit_export_logs')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按导出单号、文件名、操作人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('export_no', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('operator', 'like', "%{$search}%");
            });
        }
        // 按导出模块筛选
        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }
        // 按状态筛选（success/failed）
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按导出时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('exported_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按导出时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('exported_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按导出时间倒序分页，每页20条
        $exportLogs = $query->orderByDesc('exported_at')->paginate(20);

        // 统计数据：基于当前租户的导出日志总数及各状态数量
        $baseQuery = DB::table('soho_audit_export_logs')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'   => (clone $baseQuery)->count(),
            'success' => (clone $baseQuery)->where('status', 'success')->count(),
            'failed'  => (clone $baseQuery)->where('status', 'failed')->count(),
            'today'   => (clone $baseQuery)->whereDate('exported_at', today())->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'success' => '成功',
            'failed'  => '失败',
        ];

        // 导出模块筛选选项（常见业务模块）
        $moduleOptions = [
            'order'      => '订单',
            'product'    => '商品',
            'finance'    => '财务',
            'inventory'  => '库存',
            'customer'   => '客户',
            'report'     => '报表',
        ];

        return view('audit.export-logs.index', compact('exportLogs', 'stats', 'statusOptions', 'moduleOptions'));
    }
}
