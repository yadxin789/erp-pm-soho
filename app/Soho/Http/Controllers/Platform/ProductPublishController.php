<?php

namespace App\Soho\Http\Controllers\Platform;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 平台商品发布控制器
 * 负责将内部商品发布到各外部平台，支持按平台、发布状态筛选
 */
class ProductPublishController extends Controller
{
    /**
     * 商品发布列表
     * 展示当前租户下的商品发布记录，支持关键词搜索与状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询商品发布记录，限定当前租户
        $query = DB::table('product_publishes')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按发布单号、商品 SPU、商品名称模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('publish_no', 'like', "%{$search}%")
                  ->orWhere('spu', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }
        // 按目标平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按发布状态筛选
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
        $publishes = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的发布记录总数及各状态数量
        $baseQuery = DB::table('product_publishes')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'pending'   => (clone $baseQuery)->where('status', 'pending')->count(),
            'success'   => (clone $baseQuery)->where('status', 'success')->count(),
            'failed'    => (clone $baseQuery)->where('status', 'failed')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'  => '待发布',
            'success'  => '发布成功',
            'failed'   => '发布失败',
            'auditing' => '审核中',
        ];

        return view('platform.product-publish.index', compact('publishes', 'stats', 'statusOptions'));
    }
}
