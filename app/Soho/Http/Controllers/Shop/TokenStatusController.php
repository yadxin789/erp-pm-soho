<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Soho\Models\Shop;
use App\Soho\Models\PlatformAccount;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Token状态控制器
 * 用于监控和管理平台Token的有效性
 */
class TokenStatusController extends Controller
{
    /**
     * 显示Token状态列表
     * 展示所有店铺和平台账号的Token状态信息
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 查询店铺Token状态
        $shops = Shop::with(['credential'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('shop_name', 'like', "%{$search}%")
                      ->orWhere('shop_code', 'like', "%{$search}%")
                      ->orWhere('platform', 'like', "%{$search}%");
                });
            })
            ->when($platform = $request->input('platform'), function ($query) use ($platform) {
                $query->where('platform', $platform);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($shop) {
                // 计算Token状态
                $tokenStatus = $this->calculateTokenStatus($shop);
                $shop->token_status = $tokenStatus;
                return $shop;
            });

        // 查询平台账号Token状态
        $platformAccounts = PlatformAccount::with(['createdBy:id,name,email'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_name', 'like', "%{$search}%")
                      ->orWhere('platform_name', 'like', "%{$search}%")
                      ->orWhere('developer_id', 'like', "%{$search}%");
                });
            })
            ->when($platformCode = $request->input('platform_code'), function ($query) use ($platformCode) {
                $query->where('platform_code', $platformCode);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($account) {
                // 计算Token状态
                $tokenStatus = $this->calculatePlatformTokenStatus($account);
                $account->token_status = $tokenStatus;
                return $account;
            });

        // 统计数据
        $statistics = [
            'shops' => [
                'total' => $shops->count(),
                'valid' => $shops->where('token_status.status', 'valid')->count(),
                'expiring_soon' => $shops->where('token_status.status', 'expiring_soon')->count(),
                'expired' => $shops->where('token_status.status', 'expired')->count(),
                'no_token' => $shops->where('token_status.status', 'no_token')->count(),
            ],
            'platform_accounts' => [
                'total' => $platformAccounts->count(),
                'valid' => $platformAccounts->where('token_status.status', 'valid')->count(),
                'expiring_soon' => $platformAccounts->where('token_status.status', 'expiring_soon')->count(),
                'expired' => $platformAccounts->where('token_status.status', 'expired')->count(),
                'no_token' => $platformAccounts->where('token_status.status', 'no_token')->count(),
            ],
        ];

        // 平台列表（用于筛选）
        $platforms = Shop::distinct()->pluck('platform')->sort()->values();
        $platformCodes = PlatformAccount::distinct()->pluck('platform_code')->sort()->values();

        return view('shop.token-status.index', compact(
            'shops',
            'platformAccounts',
            'statistics',
            'platforms',
            'platformCodes'
        ));
    }

    /**
     * 计算店铺Token状态
     */
    private function calculateTokenStatus(Shop $shop): array
    {
        $expiresAt = $shop->credential ? $shop->credential->token_expires_at : null;

        if (!$expiresAt) {
            return [
                'status' => 'no_token',
                'label' => '无Token',
                'color' => 'secondary',
                'message' => '店铺未设置Token',
            ];
        }

        $now = now();
        $daysUntilExpiry = $now->diffInDays($expiresAt, false);

        if ($daysUntilExpiry < 0) {
            return [
                'status' => 'expired',
                'label' => '已过期',
                'color' => 'danger',
                'message' => 'Token已过期',
                'days_until_expiry' => $daysUntilExpiry,
            ];
        } elseif ($daysUntilExpiry <= 7) {
            return [
                'status' => 'expiring_soon',
                'label' => '即将过期',
                'color' => 'warning',
                'message' => "Token将在{$daysUntilExpiry}天后过期",
                'days_until_expiry' => $daysUntilExpiry,
            ];
        } else {
            return [
                'status' => 'valid',
                'label' => '有效',
                'color' => 'success',
                'message' => 'Token有效',
                'days_until_expiry' => $daysUntilExpiry,
            ];
        }
    }

    /**
     * 计算平台账号Token状态
     */
    private function calculatePlatformTokenStatus(PlatformAccount $account): array
    {
        $expiresAt = $account->token_expires_at;

        if (!$expiresAt) {
            return [
                'status' => 'no_token',
                'label' => '无Token',
                'color' => 'secondary',
                'message' => '平台账号未设置Token',
            ];
        }

        $now = now();
        $daysUntilExpiry = $now->diffInDays($expiresAt, false);

        if ($daysUntilExpiry < 0) {
            return [
                'status' => 'expired',
                'label' => '已过期',
                'color' => 'danger',
                'message' => 'Token已过期',
                'days_until_expiry' => $daysUntilExpiry,
            ];
        } elseif ($daysUntilExpiry <= 7) {
            return [
                'status' => 'expiring_soon',
                'label' => '即将过期',
                'color' => 'warning',
                'message' => "Token将在{$daysUntilExpiry}天后过期",
                'days_until_expiry' => $daysUntilExpiry,
            ];
        } else {
            return [
                'status' => 'valid',
                'label' => '有效',
                'color' => 'success',
                'message' => 'Token有效',
                'days_until_expiry' => $daysUntilExpiry,
            ];
        }
    }
}