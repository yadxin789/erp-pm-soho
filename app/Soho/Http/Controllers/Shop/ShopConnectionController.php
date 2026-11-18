<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Soho\Models\Shop;
use App\Soho\Models\PlatformAccount;
use Illuminate\Support\Facades\Auth;

/**
 * 店铺连接状态控制器
 * 用于监控店铺与平台的连接状态
 */
class ShopConnectionController extends Controller
{
    /**
     * 显示店铺连接状态列表
     * 展示所有店铺的连接状态，包括认证状态、同步状态、最近API调用等
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 查询店铺连接状态
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
                // 计算连接状态
                $connectionStatus = $this->calculateConnectionStatus($shop);
                $shop->connection_status = $connectionStatus;
                return $shop;
            });

        // 查询平台账号连接状态
        $platformAccounts = PlatformAccount::query()
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
                // 计算连接状态
                $connectionStatus = $this->calculatePlatformConnectionStatus($account);
                $account->connection_status = $connectionStatus;
                return $account;
            });

        // 统计数据
        $statistics = [
            'shops' => [
                'total' => $shops->count(),
                'connected' => $shops->where('connection_status.status', 'connected')->count(),
                'disconnected' => $shops->where('connection_status.status', 'disconnected')->count(),
                'degraded' => $shops->where('connection_status.status', 'degraded')->count(),
                'unknown' => $shops->where('connection_status.status', 'unknown')->count(),
            ],
            'platform_accounts' => [
                'total' => $platformAccounts->count(),
                'connected' => $platformAccounts->where('connection_status.status', 'connected')->count(),
                'disconnected' => $platformAccounts->where('connection_status.status', 'disconnected')->count(),
                'degraded' => $platformAccounts->where('connection_status.status', 'degraded')->count(),
                'unknown' => $platformAccounts->where('connection_status.status', 'unknown')->count(),
            ],
        ];

        // 平台列表（用于筛选）
        $platforms = Shop::distinct()->pluck('platform')->sort()->values();
        $platformCodes = PlatformAccount::distinct()->pluck('platform_code')->sort()->values();

        return view('shop.shop-connection.index', compact(
            'shops',
            'platformAccounts',
            'statistics',
            'platforms',
            'platformCodes'
        ));
    }

    /**
     * 计算店铺连接状态
     */
    private function calculateConnectionStatus(Shop $shop): array
    {
        // 检查是否启用
        if (!$shop->status) {
            return [
                'status' => 'disconnected',
                'label' => '已停用',
                'color' => 'secondary',
                'message' => '店铺已停用',
            ];
        }

        // 检查授权状态
        if ($shop->auth_status === 0) {
            return [
                'status' => 'disconnected',
                'label' => '未授权',
                'color' => 'danger',
                'message' => '店铺未授权，无法连接',
            ];
        }

        if ($shop->auth_status === 2) {
            return [
                'status' => 'disconnected',
                'label' => '授权过期',
                'color' => 'danger',
                'message' => '店铺授权已过期，需要重新授权',
            ];
        }

        // 检查Token状态
        $expiresAt = $shop->credential ? $shop->credential->token_expires_at : null;
        if ($expiresAt) {
            $daysUntilExpiry = now()->diffInDays($expiresAt, false);
            if ($daysUntilExpiry < 0) {
                return [
                    'status' => 'disconnected',
                    'label' => 'Token已过期',
                    'color' => 'danger',
                    'message' => 'Token已过期，需要刷新或重新授权',
                ];
            } elseif ($daysUntilExpiry <= 7) {
                return [
                    'status' => 'degraded',
                    'label' => 'Token即将过期',
                    'color' => 'warning',
                    'message' => "Token将在{$daysUntilExpiry}天后过期",
                ];
            }
        } else {
            return [
                'status' => 'unknown',
                'label' => '无Token信息',
                'color' => 'secondary',
                'message' => '无Token信息，无法判断连接状态',
            ];
        }

        // 检查同步开关
        if (!$shop->sync_enabled) {
            return [
                'status' => 'degraded',
                'label' => '同步已关闭',
                'color' => 'warning',
                'message' => '同步开关已关闭，数据不会自动同步',
            ];
        }

        return [
            'status' => 'connected',
            'label' => '连接正常',
            'color' => 'success',
            'message' => '店铺连接状态正常',
        ];
    }

    /**
     * 计算平台账号连接状态
     */
    private function calculatePlatformConnectionStatus(PlatformAccount $account): array
    {
        // 检查账号状态
        if ($account->status === 'inactive') {
            return [
                'status' => 'disconnected',
                'label' => '已停用',
                'color' => 'secondary',
                'message' => '平台账号已停用',
            ];
        }

        if ($account->status === 'suspended') {
            return [
                'status' => 'disconnected',
                'label' => '已暂停',
                'color' => 'danger',
                'message' => '平台账号已暂停',
            ];
        }

        // 检查OAuth授权状态
        if ($account->oauth_status === 'unauthorized') {
            return [
                'status' => 'disconnected',
                'label' => '未授权',
                'color' => 'danger',
                'message' => '平台账号未授权',
            ];
        }

        if ($account->oauth_status === 'revoked') {
            return [
                'status' => 'disconnected',
                'label' => '授权已撤销',
                'color' => 'danger',
                'message' => '平台账号授权已被撤销',
            ];
        }

        if ($account->oauth_status === 'expired') {
            return [
                'status' => 'degraded',
                'label' => '授权已过期',
                'color' => 'warning',
                'message' => '平台账号授权已过期，需要重新授权',
            ];
        }

        // 检查Token状态
        $expiresAt = $account->token_expires_at;
        if ($expiresAt) {
            $daysUntilExpiry = now()->diffInDays($expiresAt, false);
            if ($daysUntilExpiry < 0) {
                return [
                    'status' => 'disconnected',
                    'label' => 'Token已过期',
                    'color' => 'danger',
                    'message' => 'Token已过期，需要刷新',
                ];
            } elseif ($daysUntilExpiry <= 7) {
                return [
                    'status' => 'degraded',
                    'label' => 'Token即将过期',
                    'color' => 'warning',
                    'message' => "Token将在{$daysUntilExpiry}天后过期",
                ];
            }
        }

        return [
            'status' => 'connected',
            'label' => '连接正常',
            'color' => 'success',
            'message' => '平台账号连接状态正常',
        ];
    }
}