<?php

namespace App\Soho\Http\Controllers\Api\Shop;

use App\Soho\Models\Shop;
use App\Soho\Services\PlatformServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 店铺API控制器
 */
class ShopController extends Controller
{
    /**
     * 获取店铺列表
     */
    public function index(Request $request)
    {
        $shops = Shop::query();

        if ($request->has('platform')) {
            $shops->where('platform', $request->platform);
        }
        if ($request->has('status')) {
            $shops->where('status', $request->status);
        }

        return response()->json([
            'data' => $shops->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 获取店铺详情
     */
    public function show(Shop $shop)
    {
        return response()->json([
            'data' => $shop,
        ]);
    }

    /**
     * 创建店铺
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:taobao,jd,pinduoduo,amazon',
            'shop_name' => 'required|string|max:200',
            'shop_code' => 'nullable|string|max:100',
            'shop_url' => 'nullable|url|max:500',
            'status' => 'boolean',
            'sync_enabled' => 'boolean',
            'remark' => 'nullable|string|max:500',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $shop = Shop::create($validated);

        return response()->json([
            'data' => $shop,
            'message' => '店铺创建成功',
        ], 201);
    }

    /**
     * 更新店铺
     */
    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'shop_name' => 'sometimes|string|max:200',
            'shop_code' => 'sometimes|nullable|string|max:100',
            'shop_url' => 'sometimes|nullable|url|max:500',
            'status' => 'sometimes|boolean',
            'sync_enabled' => 'sometimes|boolean',
            'remark' => 'sometimes|nullable|string|max:500',
        ]);

        $shop->update($validated);

        return response()->json([
            'data' => $shop,
            'message' => '店铺更新成功',
        ]);
    }

    /**
     * 删除店铺
     */
    public function destroy(Shop $shop)
    {
        $shop->delete();

        return response()->json([
            'message' => '店铺删除成功',
        ]);
    }

    /**
     * 检测店铺授权状态
     */
    public function testAuth(Shop $shop)
    {
        $service = PlatformServiceFactory::create($shop);
        $isAuthorized = $service->testAuthorization();

        return response()->json([
            'data' => [
                'shop_id' => $shop->id,
                'authorized' => $isAuthorized,
                'auth_status' => $shop->auth_status,
            ],
        ]);
    }

    /**
     * 刷新店铺Token
     */
    public function refreshToken(Shop $shop)
    {
        $service = PlatformServiceFactory::create($shop);
        $isRefreshed = $service->refreshAccessToken();

        return response()->json([
            'data' => [
                'shop_id' => $shop->id,
                'refreshed' => $isRefreshed,
                'token_expires_at' => $shop->token_expires_at,
            ],
        ]);
    }
}