<?php

namespace App\Soho\Http\Controllers\Audit;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 对账差异审计控制器
 * 负责审计财务对账差异记录，支持按平台、差异类型、处理状态筛选
 */
class ReconciliationDiffController extends Controller
{
    /**
     * 对账差异审计列表
     * 展示当前租户下的对账差异记录，支持按对账单号、平台、差异类型、状态筛选，并返回统计数据
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于多租户数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 查询对账差异审计记录，限定当前租户
        $query = DB::table('soho_audit_reconciliation_diffs')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键词搜索：支持按对账单号模糊匹配
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reconciliation_no', 'like', "%{$search}%");
            });
        }
        // 按平台筛选
        if ($platform = $request->input('platform')) {
            $query->where('platform', $platform);
        }
        // 按差异类型筛选
        if ($diffType = $request->input('diff_type')) {
            $query->where('diff_type', $diffType);
        }
        // 按处理状态筛选（pending/resolved）
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
        $reconciliationDiffs = $query->orderByDesc('created_at')->paginate(20);

        // 统计数据：基于当前租户的对账差异总数及各状态、金额合计
        $baseQuery = DB::table('soho_audit_reconciliation_diffs')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $stats = [
            'total'         => (clone $baseQuery)->count(),
            'pending'       => (clone $baseQuery)->where('status', 'pending')->count(),
            'resolved'      => (clone $baseQuery)->where('status', 'resolved')->count(),
            'total_amount'  => (clone $baseQuery)->sum('diff_amount'),
        ];

        // 处理状态筛选选项
        $statusOptions = [
            'pending'  => '待处理',
            'resolved' => '已处理',
        ];

        // 差异类型筛选选项（常见对账差异类型）
        $diffTypeOptions = [
            'amount_mismatch' => '金额不一致',
            'missing_order'   => '订单缺失',
            'duplicate_order' => '订单重复',
            'fee_error'       => '费用错误',
            'refund_error'    => '退款错误',
        ];

        // 平台筛选选项（常见电商平台）
        $platformOptions = [
            'shopify'    => 'Shopify',
            'amazon'     => 'Amazon',
            'ebay'       => 'eBay',
            'walmart'    => 'Walmart',
            'tiktok'     => 'TikTok Shop',
            'aliexpress' => 'AliExpress',
        ];

        return view('audit.reconciliation-diffs.index', compact('reconciliationDiffs', 'stats', 'statusOptions', 'diffTypeOptions', 'platformOptions'));
    }
}
