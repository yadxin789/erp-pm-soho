<?php

namespace App\Soho\Http\Controllers\Shop;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Soho\Models\Shop;
use App\Soho\Models\PlatformAccount;
use Illuminate\Support\Facades\Auth;

/**
 * 授权状态控制器
 * 用于监控店铺和平台账号的授权状态
 */
class AuthorizationStatusController extends Controller
{
    /**
     * 显示授权状态列表
     * 展示所有店铺和平台账号的授权状态信息
     */
    public function index(Request $request)
    {
        // 获取当前用户的租户ID
        $tenantId = Auth::user()->tenant_id;

        // 查询店铺授权状态
        $shops = Shop::query()
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
            ->when($authStatus = $request->input('auth_status'), function ($query) use ($authStatus) {
                $query->where('auth_status', $authStatus);
            })
            ->orderByDesc('created_at')
            ->get();

        // 查询平台账号授权状态
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
            ->when($oauthStatus = $request->input('oauth_status'), function ($query) use ($oauthStatus) {
                $query->where('oauth_status', $oauthStatus);
            })
            ->orderByDesc('created_at')
            ->get();

        // 统计数据
        $statistics = [
            'shops' => [
                'total' => $shops->count(),
                'authorized' => $shops->where('auth_status', 1)->count(),
                'unauthorized' => $shops->where('auth_status', 0)->count(),
                'expired' => $shops->where('auth_status', 2)->count(),
            ],
            'platform_accounts' => [
                'total' => $platformAccounts->count(),
                'authorized' => $platformAccounts->where('oauth_status', 'authorized')->count(),
                'unauthorized' => $platformAccounts->where('oauth_status', 'unauthorized')->count(),
                'expired' => $platformAccounts->where('oauth_status', 'expired')->count(),
                'revoked' => $platformAccounts->where('oauth_status', 'revoked')->count(),
            ],
        ];

        // 平台列表（用于筛选）
        $platforms = Shop::distinct()->pluck('platform')->sort()->values();
        $platformCodes = PlatformAccount::distinct()->pluck('platform_code')->sort()->values();

        // 授权状态标签
        $authStatusLabels = [
            0 => '未授权',
            1 => '已授权',
            2 => '已过期',
        ];

        $oauthStatusLabels = [
            'authorized' => '已授权',
            'unauthorized' => '未授权',
            'expired' => '已过期',
            'revoked' => '已撤销',
        ];

        return view('shop.authorization-status.index', compact(
            'shops',
            'platformAccounts',
            'statistics',
            'platforms',
            'platformCodes',
            'authStatusLabels',
            'oauthStatusLabels'
        ));
    }

    /**
     * 显示单个店铺的授权详情
     */
    public function showShop(Shop $shop)
    {
        $shop->load(['credential']);

        return view('shop.authorization-status.show-shop', compact('shop'));
    }

    /**
     * 显示单个平台账号的授权详情
     */
    public function showPlatformAccount(PlatformAccount $platformAccount)
    {
        $platformAccount->load(['createdBy:id,name,email']);

        return view('shop.authorization-status.show-platform-account', compact('platformAccount'));
    }
}