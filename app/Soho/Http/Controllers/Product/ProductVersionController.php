<?php

namespace App\Soho\Http\Controllers\Product;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 商品版本控制器
 * 用于管理商品版本历史的列表展示与筛选
 */
class ProductVersionController extends Controller
{
    /**
     * 商品版本列表
     * 支持按商品名称、版本号、操作类型筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 商品版本列表查询：使用 when 条件查询实现动态筛选
        $versions = DB::table('soho_product_versions')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                // 关键字搜索：商品名称、商品编码
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->when($versionNo = $request->input('version_no'), function ($query) use ($versionNo) {
                $query->where('version_no', 'like', "%{$versionNo}%");
            })
            ->when($changeType = $request->input('change_type'), function ($query) use ($changeType) {
                $query->where('change_type', $changeType);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        // 变更类型字典
        $changeTypeOptions = [
            'create' => '创建',
            'update' => '更新',
            'publish' => '发布',
            'offline' => '下架',
        ];

        // 统计卡片数据
        $stats = [
            'total' => DB::table('soho_product_versions')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->count(),
            'today' => DB::table('soho_product_versions')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('product.versions.index', compact('versions', 'changeTypeOptions', 'stats'));
    }
}
