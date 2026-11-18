<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Shop;
use App\Soho\Models\ShopCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    /**
     * 店铺列表
     */
    public function index(Request $request)
    {
        $shops = Shop::with('credential')
            ->when($request->search, function($query) use ($request) {
                $query->where('shop_name', 'like', "%{$request->search}%")
                    ->orWhere('platform', 'like', "%{$request->search}%");
            })
            ->when($request->platform, function($query) use ($request) {
                $query->where('platform', $request->platform);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('shop.index', compact('shops'));
    }

    /**
     * 创建店铺
     */
    public function create()
    {
        return view('shop.create');
    }

    /**
     * 保存新店铺
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'platform' => 'required|string|max:50',
            'shop_code' => 'required|string|max:255',
            'app_key' => 'nullable|string',
            'app_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
            'refresh_token' => 'nullable|string',
            'shop_url' => 'nullable|string|max:255',
            'sync_enabled' => 'boolean',
            'remark' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $validated['status'] = $validated['status'] ?? true;
        $validated['auth_status'] = !empty($validated['access_token']) ? 1 : 0;

        // 提取凭证字段
        $credentialData = [
            'access_token' => $validated['access_token'] ?? null,
            'refresh_token' => $validated['refresh_token'] ?? null,
        ];
        unset($validated['access_token'], $validated['refresh_token']);

        $shop = Shop::create($validated);

        // 创建凭证记录
        if (!empty($credentialData['access_token'])) {
            $shop->credential()->create([
                'access_token_encrypted' => encrypt($credentialData['access_token']),
                'refresh_token_encrypted' => encrypt($credentialData['refresh_token'] ?? ''),
                'token_expires_at' => now()->addDays(30),
                'token_type' => 'Bearer',
            ]);
        }

        return redirect()->route('soho.shop.index')->with('success', '店铺创建成功');
    }

    /**
     * 编辑店铺
     */
    public function edit(Shop $shop)
    {
        $shop->load('credential');
        return view('shop.edit', compact('shop'));
    }

    /**
     * 更新店铺
     */
    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'platform' => 'required|string|max:50',
            'shop_code' => ['required', 'string', 'max:255', Rule::unique('soho_shops')->ignore($shop->id)],
            'app_key' => 'nullable|string',
            'app_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
            'refresh_token' => 'nullable|string',
            'shop_url' => 'nullable|string|max:255',
            'sync_enabled' => 'boolean',
            'remark' => 'nullable|string',
            'status' => 'boolean',
        ]);

        // 处理 Token 更新
        if (!empty($validated['access_token'])) {
            $shop->credential()->updateOrCreate([], [
                'access_token_encrypted' => encrypt($validated['access_token']),
                'refresh_token_encrypted' => encrypt($validated['refresh_token'] ?? ''),
                'token_expires_at' => now()->addDays(30),
                'token_type' => 'Bearer',
                'last_refresh_at' => now(),
            ]);
            $validated['auth_status'] = 1;
        }
        unset($validated['access_token'], $validated['refresh_token']);

        $shop->update($validated);

        return redirect()->route('soho.shop.index')->with('success', '店铺更新成功');
    }

    /**
     * 删除店铺
     */
    public function destroy(Shop $shop)
    {
        if ($shop->platformOrders()->count() > 0) {
            return back()->with('error', '该店铺下有订单，无法删除');
        }

        $shop->delete();
        return redirect()->route('soho.shop.index')->with('success', '店铺删除成功');
    }

    /**
     * 店铺授权管理页面
     * 集中展示所有店铺的授权状态，并提供授权操作入口
     */
    public function authorize(Request $request)
    {
        // 获取店铺列表（含凭证信息），支持按平台和授权状态筛选
        $shops = Shop::with('credential')
            ->when($request->platform, function ($query) use ($request) {
                $query->where('platform', $request->platform);
            })
            ->when($request->auth_status !== null && $request->auth_status !== '', function ($query) use ($request) {
                $query->where('auth_status', $request->auth_status);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('shop_name', 'like', "%{$request->search}%")
                    ->orWhere('shop_code', 'like', "%{$request->search}%");
            })
            ->orderBy('platform')
            ->orderByDesc('created_at')
            ->paginate(20);

        // 统计数据
        $stats = [
            'total' => Shop::count(),
            'authorized' => Shop::where('auth_status', 1)->count(),
            'unauthorized' => Shop::where('auth_status', 0)->count(),
            'expiring' => Shop::whereHas('credential', function ($q) {
                $q->where('token_expires_at', '<=', now()->addDays(7))
                    ->where('token_expires_at', '>', now());
            })->count(),
        ];

        return view('shop.authorize', compact('shops', 'stats'));
    }

    /**
     * 检测授权状态 - 通过调用平台 API 验证 Token 有效性
     */
    public function checkAuth(Shop $shop)
    {
        try {
            $accessToken = $shop->access_token;
            if (empty($accessToken)) {
                return back()->with('error', '店铺尚未配置 Access Token');
            }

            // 根据平台构造验证请求
            $response = match($shop->platform) {
                'taobao' => Http::timeout(10)->get('https://openapi.taobao.com/router/request', [
                    'method' => 'taobao.user.seller.get',
                    'session' => $accessToken,
                ]),
                'jd' => Http::timeout(10)->withHeaders([
                    'access_token' => $accessToken,
                ])->get('https://api.jd.com/routerjson', [
                    'method' => 'jingdong.user.seller.get',
                ]),
                'pinduoduo' => Http::timeout(10)->get('https://gw-api.pinduoduo.com/api/router', [
                    'type' => 'pdd.user.seller.get',
                    'access_token' => $accessToken,
                ]),
                default => throw new \Exception("不支持的平台：{$shop->platform}"),
            };

            if ($response->successful()) {
                $data = $response->json();
                // 判断响应中是否包含错误码
                $hasError = isset($data['error_response']) || isset($data['error_response']);

                if ($hasError) {
                    $shop->update(['auth_status' => 0]);
                    return back()->with('error', '授权验证失败：Token 可能已过期');
                }

                $shop->update(['auth_status' => 1]);
                return back()->with('success', '授权验证成功，Token 有效');
            }

            $shop->update(['auth_status' => 0]);
            return back()->with('error', '授权验证失败：HTTP ' . $response->status());

        } catch (\Exception $e) {
            Log::error("店铺授权检测失败: {$shop->id}, " . $e->getMessage());
            $shop->update(['auth_status' => 0]);
            return back()->with('error', '授权检测异常：' . $e->getMessage());
        }
    }

    /**
     * 刷新 Token - 调用平台接口获取新的 Access Token
     */
    public function refreshToken(Shop $shop)
    {
        try {
            $refreshToken = $shop->refresh_token;
            if (empty($refreshToken)) {
                return back()->with('error', '缺少 Refresh Token，无法刷新');
            }

            if (empty($shop->app_key) || empty($shop->app_secret)) {
                return back()->with('error', '缺少 AppKey/AppSecret，无法刷新 Token');
            }

            // 根据平台构造刷新请求
            $response = match($shop->platform) {
                'taobao' => Http::timeout(15)->post('https://oauth2.taobao.com/refresh_token', [
                    'grant_type' => 'refresh_token',
                    'client_id' => $shop->app_key,
                    'client_secret' => $shop->app_secret,
                    'refresh_token' => $refreshToken,
                ]),
                'jd' => Http::timeout(15)->post('https://oauth.jd.com/oauth20/token', [
                    'grant_type' => 'refresh_token',
                    'client_id' => $shop->app_key,
                    'client_secret' => $shop->app_secret,
                    'refresh_token' => $refreshToken,
                ]),
                'pinduoduo' => Http::timeout(15)->post('https://gw-api.pinduoduo.com/api/router', [
                    'type' => 'pdd.oauth2.access.token.get',
                    'grant_type' => 'refresh_token',
                    'client_id' => $shop->app_key,
                    'client_secret' => $shop->app_secret,
                    'refresh_token' => $refreshToken,
                ]),
                default => throw new \Exception("不支持的平台：{$shop->platform}"),
            };

            if ($response->successful()) {
                $data = $response->json();

                // 解析各平台响应格式
                $newToken = $data['access_token'] ?? null;
                $newRefreshToken = $data['refresh_token'] ?? $refreshToken;
                $expiresIn = $data['expires_in'] ?? 86400;

                if (empty($newToken)) {
                    return back()->with('error', 'Token 刷新失败：响应数据异常');
                }

                // 更新凭证
                $shop->updateCredentials([
                    'access_token' => $newToken,
                    'refresh_token' => $newRefreshToken,
                    'expires_in' => $expiresIn,
                ]);

                $shop->update(['auth_status' => 1]);

                return back()->with('success', 'Token 刷新成功，有效期：' . round($expiresIn / 3600) . ' 小时');
            }

            return back()->with('error', 'Token 刷新失败：HTTP ' . $response->status());

        } catch (\Exception $e) {
            Log::error("Token 刷新失败: {$shop->id}, " . $e->getMessage());
            return back()->with('error', 'Token 刷新异常：' . $e->getMessage());
        }
    }
}