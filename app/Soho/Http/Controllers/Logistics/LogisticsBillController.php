<?php

namespace App\Soho\Http\Controllers\Logistics;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 物流账单控制器
 * 用于查询物流账单，支持按账单号、快递公司、时间范围筛选
 */
class LogisticsBillController extends Controller
{
    /**
     * 物流账单列表
     * 支持按账单号、快递公司、对账状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建物流账单查询
        $query = DB::table('soho_logistics_bills')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键字搜索：账单号、快递公司账单号
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('carrier_bill_no', 'like', "%{$search}%");
            });
        }
        // 快递公司筛选
        if ($expressCompany = $request->input('express_company')) {
            $query->where('express_company', $expressCompany);
        }
        // 对账状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 账单起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('bill_date', '>=', $dateFrom . ' 00:00:00');
        }
        // 账单结束日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('bill_date', '<=', $dateTo . ' 23:59:59');
        }

        // 分页查询账单列表
        $bills = $query->orderByDesc('bill_date')->paginate(20);

        // 统计各对账状态账单数量及金额
        $stats = [
            // 总账单数
            'total' => DB::table('soho_logistics_bills')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            // 已对账账单数
            'reconciled' => DB::table('soho_logistics_bills')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('status', 'reconciled')->count(),
            // 待对账账单数
            'pending' => DB::table('soho_logistics_bills')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('status', 'pending')->count(),
            // 有差异账单数
            'disputed' => DB::table('soho_logistics_bills')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('status', 'disputed')->count(),
            // 账单总金额
            'total_amount' => DB::table('soho_logistics_bills')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->sum('total_amount'),
        ];

        // 对账状态选项
        $statusOptions = ['pending' => '待对账', 'reconciled' => '已对账', 'disputed' => '有差异'];
        // 快递公司选项
        $expressOptions = ['SF' => '顺丰速运', 'YTO' => '圆通速递', 'ZTO' => '中通快递', 'STO' => '申通快递', 'YD' => '韵达快递', 'JD' => '京东物流'];

        return view('logistics.bills.index', compact('bills', 'stats', 'statusOptions', 'expressOptions'));
    }
}
