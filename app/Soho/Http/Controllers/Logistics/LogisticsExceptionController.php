<?php

namespace App\Soho\Http\Controllers\Logistics;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 异常物流控制器
 * 用于查询异常物流件，支持按异常类型、级别、状态筛选
 */
class LogisticsExceptionController extends Controller
{
    /**
     * 异常物流列表
     * 支持按异常类型、级别、状态、时间范围筛选
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取当前登录用户的租户ID，用于数据隔离
        $tenantId = Auth::user()->tenant_id;

        // 构建异常物流查询
        $query = DB::table('soho_logistics_exceptions')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // 关键字搜索：运单号、跟踪号、订单号
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('waybill_no', 'like', "%{$search}%")
                  ->orWhere('tracking_no', 'like', "%{$search}%")
                  ->orWhere('order_no', 'like', "%{$search}%");
            });
        }
        // 异常类型筛选
        if ($exceptionType = $request->input('exception_type')) {
            $query->where('exception_type', $exceptionType);
        }
        // 异常级别筛选
        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }
        // 处理状态筛选
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        // 异常发生起始日期筛选
        if ($dateFrom = $request->input('date_from')) {
            $query->where('occurred_at', '>=', $dateFrom . ' 00:00:00');
        }
        // 异常发生结束日期筛选
        if ($dateTo = $request->input('date_to')) {
            $query->where('occurred_at', '<=', $dateTo . ' 23:59:59');
        }

        // 分页查询异常物流列表（按级别降序、最近发生优先）
        $exceptions = $query->orderByDesc('severity')->orderByDesc('occurred_at')->paginate(20);

        // 统计各处理状态异常数量
        $stats = [
            // 总异常数
            'total' => DB::table('soho_logistics_exceptions')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count(),
            // 待处理异常数
            'pending' => DB::table('soho_logistics_exceptions')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('status', 'pending')->count(),
            // 处理中异常数
            'processing' => DB::table('soho_logistics_exceptions')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('status', 'processing')->count(),
            // 已解决异常数
            'resolved' => DB::table('soho_logistics_exceptions')->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->where('status', 'resolved')->count(),
        ];

        // 处理状态选项
        $statusOptions = ['pending' => '待处理', 'processing' => '处理中', 'resolved' => '已解决'];
        // 异常类型选项
        $exceptionTypeOptions = [
            'lost' => '丢件',
            'damaged' => '破损',
            'wrong_address' => '地址错误',
            'rejected' => '拒收',
            'delayed' => '延误',
        ];
        // 异常级别选项
        $severityOptions = ['low' => '低', 'medium' => '中', 'high' => '高', 'urgent' => '紧急'];

        return view('logistics.exceptions.index', compact('exceptions', 'stats', 'statusOptions', 'exceptionTypeOptions', 'severityOptions'));
    }
}
