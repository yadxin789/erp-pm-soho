<?php

namespace App\Soho\Http\Controllers\Finance;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 平台结算控制器
 * 用于查询平台结算单，支持按结算单号、平台、结算周期、状态筛选
 */
class PlatformSettlementController extends Controller
{
    /**
     * 平台结算列表
     * 支持按结算单号、平台、状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建平台结算查询，限定当前租户
        $query = DB::table('soho_platform_settlements')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按结算单号模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('settlement_no', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%");
            });
        }
        // 按平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按结算状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 按结算周期起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('period_start', '>=', $dateFrom . ' 00:00:00');
        }
        // 按结算周期截止日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('period_end', '<=', $dateTo . ' 23:59:59');
        }

        // 按创建时间倒序分页，每页20条
        $settlements = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的结算单总数及各状态数量、总金额
        $baseQuery = DB::table('soho_platform_settlements')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            // 结算单总数
            'total'        => (clone $baseQuery)->count(),
            // 待结算
            'pending'      => (clone $baseQuery)->where('status', 'pending')->count(),
            // 已结算
            'settled'      => (clone $baseQuery)->where('status', 'settled')->count(),
            // 有差异
            'disputed'     => (clone $baseQuery)->where('status', 'disputed')->count(),
            // 结算总金额
            'total_amount' => (clone $baseQuery)->sum('amount'),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'  => '待结算',
            'settled'  => '已结算',
            'disputed' => '有差异',
            'rejected' => '已驳回',
        ];

        // 平台选项
        $platformOptions = [
            'taobao'  => '淘宝',
            'tmall'   => '天猫',
            'jd'      => '京东',
            'pdd'     => '拼多多',
            'douyin'  => '抖音',
            'kuaishou' => '快手',
        ];

        return view('finance.platform-settlement.index', compact('settlements', 'stats', 'statusOptions', 'platformOptions'));
    }
}
