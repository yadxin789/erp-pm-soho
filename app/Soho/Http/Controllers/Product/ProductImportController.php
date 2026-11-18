<?php

namespace App\Soho\Http\Controllers\Product;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 商品导入控制器
 * 用于管理批量商品导入任务的列表展示与筛选
 */
class ProductImportController extends Controller
{
    /**
     * 商品导入任务列表
     * 支持按任务名称、状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 导入任务列表查询：使用 when 条件查询实现动态筛选
        $imports = DB::table('product_imports')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                // 关键字搜索：任务名称、文件名
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('file_name', 'like', "%{$search}%");
                });
            })
            ->when($status = $request->input('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom = $request->input('date_from'), function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            })
            ->when($dateTo = $request->input('date_to'), function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        // 状态选项字典
        $statusOptions = [
            'pending' => '待处理',
            'processing' => '处理中',
            'completed' => '已完成',
            'failed' => '失败',
            'partial' => '部分成功',
        ];

        // 统计卡片数据
        $stats = [
            'total' => DB::table('product_imports')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->count(),
            'completed' => DB::table('product_imports')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'completed')->count(),
            'failed' => DB::table('product_imports')->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->where('status', 'failed')->count(),
        ];

        return view('product.import.index', compact('imports', 'statusOptions', 'stats'));
    }
}
