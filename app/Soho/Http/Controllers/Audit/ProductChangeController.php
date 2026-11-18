<?php

namespace App\Soho\Http\Controllers\Audit;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 商品变更审计控制器
 * 负责审计商品信息的变更记录，支持按操作类型、操作人、商品名称筛选
 */
class ProductChangeController extends Controller
{
    /**
     * 商品变更审计列表
     * 展示当前租户下的商品变更记录，支持按商品名称、操作类型筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询商品变更审计记录，限定当前租户
        $query = DB::table('soho_audit_product_changes')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按商品名称、变更字段、操作人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('changed_field', 'like', "%{$search}%")
                  ->orWhere('operator', 'like', "%{$search}%");
            });
        }
        // 按操作类型筛选（create/update/delete）
        if ($operationType = $request->input('operation_type')) {
            $query->where('operation_type', $operationType);
        }
        // 按操作人筛选
        if ($operator = $request->input('operator')) {
            $query->where('operator', $operator);
        }
        // 按操作时间起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('operated_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 按操作时间截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('operated_at', '<=', $dateTo . ' 23:59:59');
        }

        // 按操作时间倒序分页，每页20条
        $productChanges = $query->orderByDesc('operated_at')->paginate(20);

        // 统计数据：基于当前租户的商品变更总数及各操作类型数量
        $baseQuery = DB::table('soho_audit_product_changes')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'  => (clone $baseQuery)->count(),
            'create' => (clone $baseQuery)->where('operation_type', 'create')->count(),
            'update' => (clone $baseQuery)->where('operation_type', 'update')->count(),
            'delete' => (clone $baseQuery)->where('operation_type', 'delete')->count(),
        ];

        // 操作类型筛选选项
        $operationTypeOptions = [
            'create' => '新增',
            'update' => '修改',
            'delete' => '删除',
        ];

        return view('audit.product-changes.index', compact('productChanges', 'stats', 'operationTypeOptions'));
    }
}
