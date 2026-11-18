<?php

namespace App\Soho\Http\Controllers\Aftersale;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 换货与补发控制器
 * 负责处理售后换货及补发单的全流程管理，涵盖待处理、处理中、已发货、已完成状态
 */
class ReplacementController extends Controller
{
    /**
     * 换货补发列表
     * 展示当前租户下的换货/补发单，支持按单号、类型、状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询换货/补发单，限定当前租户
        $query = DB::table('replacements')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按换货补发单号、订单号、客户名称模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('replacement_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        // 按类型筛选：换货/补发
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        // 按状态筛选
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
        $replacements = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的换货补发单总数及各状态数量
        $baseQuery = DB::table('replacements')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'pending'    => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'shipped'    => (clone $baseQuery)->where('status', 'shipped')->count(),
            'completed'  => (clone $baseQuery)->where('status', 'completed')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'    => '待处理',
            'processing' => '处理中',
            'shipped'    => '已发货',
            'completed'  => '已完成',
        ];

        // 类型筛选选项：换货/补发
        $typeOptions = [
            'exchange' => '换货',
            'reship'   => '补发',
        ];

        return view('aftersale.replacements.index', compact('replacements', 'stats', 'statusOptions', 'typeOptions'));
    }
}
