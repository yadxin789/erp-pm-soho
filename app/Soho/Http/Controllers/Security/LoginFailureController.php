<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\LoginFailure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 登录失败记录控制器
 * 展示登录失败记录，支持按IP/邮箱/时间筛选
 * 提供统计分析功能（按IP聚合、失败原因分布）
 */
class LoginFailureController extends Controller
{
    /**
     * 显示登录失败记录列表
     */
    public function index(Request $request)
    {
        $query = LoginFailure::query()->with('user:id,name,email');

        // 关键词搜索（邮箱或IP）
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // 失败原因筛选
        if ($reason = $request->input('reason')) {
            $query->where('failure_reason', $reason);
        }

        // IP地址筛选
        if ($ip = $request->input('ip')) {
            $query->where('ip_address', $ip);
        }

        // 时间范围筛选
        $hours = (int) $request->input('hours', 24);
        if ($hours > 0) {
            $query->where('created_at', '>=', now()->subHours($hours));
        }

        // 一次性获取全部记录（用户偏好：取消分页）
        $failures = $query->orderByDesc('created_at')->get();

        // 统计数据
        $stats = $this->getStatistics($hours);

        // 失败原因选项
        $reasons = LoginFailure::getReasonLabels();

        return view('security.login-failures.index', compact('failures', 'stats', 'reasons'));
    }

    /**
     * 显示单条记录详情
     */
    public function show(LoginFailure $failure)
    {
        $failure->load('user');

        // 同IP最近的其他失败记录
        $relatedFailures = LoginFailure::where('ip_address', $failure->ip_address)
            ->where('id', '!=', $failure->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('security.login-failures.show', compact('failure', 'relatedFailures'));
    }

    /**
     * 获取统计数据
     */
    private function getStatistics(int $hours): array
    {
        $since = now()->subHours($hours);

        // 总失败次数
        $total = LoginFailure::where('created_at', '>=', $since)->count();

        // 唯一IP数
        $uniqueIps = LoginFailure::where('created_at', '>=', $since)
            ->distinct('ip_address')
            ->count('ip_address');

        // 唯一邮箱数
        $uniqueEmails = LoginFailure::where('created_at', '>=', $since)
            ->whereNotNull('email')
            ->distinct('email')
            ->count('email');

        // 触发锁定次数
        $lockouts = LoginFailure::where('created_at', '>=', $since)
            ->where('triggered_lockout', true)
            ->count();

        // 按失败原因统计
        $byReason = LoginFailure::where('created_at', '>=', $since)
            ->select('failure_reason', DB::raw('count(*) as count'))
            ->groupBy('failure_reason')
            ->pluck('count', 'failure_reason')
            ->toArray();

        // 高风险IP（失败次数超过阈值）
        $highRiskIps = LoginFailure::where('created_at', '>=', $since)
            ->select('ip_address', DB::raw('count(*) as count'))
            ->groupBy('ip_address')
            ->having('count', '>=', 5)
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'total' => $total,
            'unique_ips' => $uniqueIps,
            'unique_emails' => $uniqueEmails,
            'lockouts' => $lockouts,
            'by_reason' => $byReason,
            'high_risk_ips' => $highRiskIps,
            'hours' => $hours,
        ];
    }

    /**
     * 导出失败记录（CSV）
     */
    public function export(Request $request)
    {
        // 简化实现：返回提示信息
        return back()->with('info', '导出功能开发中，敬请期待');
    }
}