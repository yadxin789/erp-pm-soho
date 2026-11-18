<?php

namespace App\Soho\Http\Controllers\Logistics;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 面单管理控制器
 * 用于管理物流面单的列表展示、筛选与统计
 */
class WaybillController extends Controller
{
    /**
     * 面单列表
     * 支持按运单号、快递公司、面单状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建面单查询（仅查询已生成面单号的包裹）
        $query = DB::table('soho_order_packages')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotNull('waybill_no');

        // 关键字搜索：运单号、跟踪号、订单号
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('waybill_no', 'like', "%{$search}%")
                  ->orWhere('tracking_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%");
            });
        }
        // 快递公司筛选
        if ($expressCompany = $request->input('express_company')) {
            $query->where('express_company', $expressCompany);
        }
        // 面单状态筛选
        if ($status = $request->input('status')) {
            $query->where('waybill_status', $status);
        }
        // 发货起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 发货结束日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // 分页查询面单列表
        $waybills = $query->orderByDesc('created_at')->paginate(20);

        // 统计各状态面单数量
        $stats = [
            // 总面单数
            'total' => DB::table('soho_order_packages')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->whereNotNull('waybill_no')->count(),
            // 已打印面单数
            'printed' => DB::table('soho_order_packages')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('waybill_status', 'printed')->count(),
            // 已发货面单数
            'shipped' => DB::table('soho_order_packages')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('waybill_status', 'shipped')->count(),
            // 打印失败面单数
            'failed' => DB::table('soho_order_packages')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('waybill_status', 'failed')->count(),
        ];

        // 面单状态选项
        $statusOptions = ['printed' => '已打印', 'shipped' => '已发货', 'failed' => '打印失败', 'void' => '已作废'];
        // 快递公司选项
        $expressOptions = ['SF' => '顺丰速运', 'YTO' => '圆通速递', 'ZTO' => '中通快递', 'STO' => '申通快递', 'YD' => '韵达快递', 'JD' => '京东物流'];

        return view('logistics.waybills.index', compact('waybills', 'stats', 'statusOptions', 'expressOptions'));
    }
}
