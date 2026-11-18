<?php

namespace App\Soho\Http\Controllers\Platform;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 平台属性管理控制器
 * 负责维护各平台的商品属性定义及其与内部属性的映射关系
 */
class AttributeController extends Controller
{
    /**
     * 平台属性列表
     * 展示当前租户下的平台属性记录，支持关键词搜索与平台筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询平台属性记录，限定当前租户
        $query = DB::table('platform_attributes')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按平台属性名、属性编码、内部分类模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('attribute_name', 'like', "%{$search}%")
                  ->orWhere('attribute_code', 'like', "%{$search}%")
                  ->orWhere('inner_category_name', 'like', "%{$search}%");
            });
        }
        // 按目标平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按是否必填筛选
        if ($required = $request->input('required')) {
            $query->where('is_required', $required);
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
        $attributes = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的平台属性总数及各必填状态数量
        $baseQuery = DB::table('platform_attributes')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'required'    => (clone $baseQuery)->where('is_required', 1)->count(),
            'optional'    => (clone $baseQuery)->where('is_required', 0)->count(),
            'mapped'      => (clone $baseQuery)->whereNotNull('inner_attribute_id')->count(),
        ];

        // 是否必填筛选选项
        $requiredOptions = [
            '1' => '必填',
            '0' => '非必填',
        ];

        return view('platform.attribute.index', compact('attributes', 'stats', 'requiredOptions'));
    }
}
