<?php

namespace App\Soho\Http\Controllers\Platform;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 平台分类映射控制器
 * 负责维护内部商品分类与各平台分类之间的映射关系
 */
class CategoryMappingController extends Controller
{
    /**
     * 分类映射列表
     * 展示当前租户下的平台分类映射，支持关键词搜索与平台筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询分类映射记录，限定当前租户
        $query = DB::table('category_mappings')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按内部分类名称、平台分类名称模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('inner_category_name', 'like', "%{$search}%")
                  ->orWhere('platform_category_name', 'like', "%{$search}%")
                  ->orWhere('platform_category_id', 'like', "%{$search}%");
            });
        }
        // 按目标平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按映射状态筛选
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
        $mappings = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的分类映射总数及各状态数量
        $baseQuery = DB::table('category_mappings')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'mapped'   => (clone $baseQuery)->where('status', 'mapped')->count(),
            'pending'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'invalid'  => (clone $baseQuery)->where('status', 'invalid')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'mapped'  => '已映射',
            'pending' => '待映射',
            'invalid' => '映射失效',
        ];

        return view('platform.category-mapping.index', compact('mappings', 'stats', 'statusOptions'));
    }
}
