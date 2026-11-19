<?php

namespace App\Soho\Http\Controllers\Product;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * SKU 管理控制器
 * 用于管理商品 SKU 的列表展示、筛选与查询
 */
class SkuController extends Controller
{
    /**
     * SKU 列表
     * 支持按 SKU 编码、条码、名称、状态筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        $table = 'soho_product_skus';

        // SKU 列表查询：使用 when 条件查询实现动态筛选
        $skus = DB::table($table)
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                // 关键字搜索：SKU 编码、条码、名称
                $query->where(function ($q) use ($search) {
                    $q->where('sku_code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($productId = $request->input('product_id'), function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // 状态选项字典
        $statusOptions = [
            'active' => '启用',
            'inactive' => '停用',
            'draft' => '草稿',
        ];

        // 统计卡片数据
        $stats = [
            'total' => DB::table($table)->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->count(),
            'active' => DB::table($table)->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'active')->count(),
            'inactive' => DB::table($table)->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'inactive')->count(),
        ];

        return view('soho.product.sku.index', compact('skus', 'statusOptions', 'stats'));
    }
}
