<?php

namespace App\Soho\Http\Controllers\Aftersale;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 退货质检控制器
 * 负责处理退货件的质检流程，包括质检登记、入库与销毁等状态管理
 */
class ReturnInspectController extends Controller
{
    /**
     * 退货质检列表
     * 展示当前租户下的退货质检单，支持按退货单号、质检状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询退货质检单，限定当前租户
        $query = DB::table('soho_return_inspections')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按退货单号模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%");
            });
        }
        // 按质检状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按创建时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按创建时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按创建时间倒序分页，每页20条
        $inspections = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的质检单总数及各状态数量
        $baseQuery = DB::table('soho_return_inspections')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'pending'    => (clone $baseQuery)->where('status', 'pending')->count(),
            'inspecting' => (clone $baseQuery)->where('status', 'inspecting')->count(),
            'passed'     => (clone $baseQuery)->where('status', 'passed')->count(),
            'destroyed'  => (clone $baseQuery)->where('status', 'destroyed')->count(),
        ];

        // 质检状态筛选选项
        $statusOptions = [
            'pending'    => '待质检',
            'inspecting' => '质检中',
            'passed'     => '质检通过入库',
            'destroyed'  => '已销毁',
        ];

        return view('aftersale.return-inspects.index', compact('inspections', 'stats', 'statusOptions'));
    }
}
