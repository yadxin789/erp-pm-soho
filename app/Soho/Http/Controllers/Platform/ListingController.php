<?php

namespace App\Soho\Http\Controllers\Platform;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Listing 刊登管理控制器
 * 负责平台商品 Listing 的优化与管理，支持按平台、店铺、状态筛选
 */
class ListingController extends Controller
{
    /**
     * Listing 列表
     * 展示当前租户下的平台刊登商品，支持关键词搜索与状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询平台刊登商品 Listing，限定当前租户
        $query = DB::table('platform_listings')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按 Listing 编号、商品 SPU、商品标题模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('listing_no', 'like', "%{$search}%")
                  ->orWhere('spu', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        // 按平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
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
        $listings = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的 Listing 总数及各状态数量
        $baseQuery = DB::table('platform_listings')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'online'   => (clone $baseQuery)->where('status', 'online')->count(),
            'offline'  => (clone $baseQuery)->where('status', 'offline')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // 状态筛选选项
        $statusOptions = [
            'draft'    => '草稿',
            'online'   => '已上线',
            'offline'  => '已下线',
            'rejected' => '已驳回',
        ];

        return view('platform.listing.index', compact('listings', 'stats', 'statusOptions'));
    }
}
