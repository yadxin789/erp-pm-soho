<?php

namespace App\Soho\Http\Controllers\Finance;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 结算审核控制器
 * 用于结算单审核流程，支持按结算单号、平台、状态、审核人筛选
 */
class SettlementAuditController extends Controller
{
    /**
     * 结算审核列表
     * 支持按结算单号、平台、状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建结算审核查询，限定当前租户
        $query = DB::table('settlement_audits')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按结算单号、审核人模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('settlement_no', 'like', "%{$search}%")
                  ->orWhere('auditor', 'like', "%{$search}%");
            });
        }
        // 按平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按审核状态筛选
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
        $audits = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的审核单总数及各状态数量、总金额
        $baseQuery = DB::table('settlement_audits')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            // 审核单总数
            'total'        => (clone $baseQuery)->count(),
            // 待审核
            'pending'      => (clone $baseQuery)->where('status', 'pending')->count(),
            // 已通过
            'approved'     => (clone $baseQuery)->where('status', 'approved')->count(),
            // 已驳回
            'rejected'     => (clone $baseQuery)->where('status', 'rejected')->count(),
            // 审核总金额
            'total_amount' => (clone $baseQuery)->sum('amount'),
        ];

        // 状态筛选选项
        $statusOptions = [
            'pending'  => '待审核',
            'approved' => '已通过',
            'rejected' => '已驳回',
        ];

        // 平台选项
        $platformOptions = [
            'taobao'   => '淘宝',
            'tmall'    => '天猫',
            'jd'       => '京东',
            'pdd'      => '拼多多',
            'douyin'   => '抖音',
            'kuaishou' => '快手',
        ];

        return view('finance.settlement-audit.index', compact('audits', 'stats', 'statusOptions', 'platformOptions'));
    }
}
