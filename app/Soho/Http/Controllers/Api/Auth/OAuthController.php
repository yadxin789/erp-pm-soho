<?php

namespace App\Soho\Http\Controllers\Api\Auth;

use App\Soho\Models\Shop;
use App\Soho\Services\PlatformServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * OAuth授权回调控制器
 * 处理各平台OAuth授权回调
 */
class OAuthController extends Controller
{
    /**
     * 生成OAuth state参数并缓存
     * 在发起授权请求前调用，返回state供前端使用
     *
     * @param int $shopId 店铺ID
     * @return string 生成的state值
     */
    public function generateState(int $shopId): string
    {
        $state = Str::random(32);
        // 缓存state，有效期10分钟
        Cache::put("oauth_state_{$state}", $shopId, 600);
        return $state;
    }

    /**
     * 处理平台授权回调
     */
    public function callback(Request $request, string $platform)
    {
        // 校验state参数，防止CSRF攻击
        $state = $request->get('state');
        $shopId = $this->validateState($state);
        if (!$shopId) {
            return response()->json(['error' => 'Invalid state'], 400);
        }

        // 获取授权码
        $code = $request->get('code');
        if (!$code) {
            return response()->json(['error' => 'Missing code'], 400);
        }

        try {
            // 根据state获取的shop_id查找店铺
            $shop = Shop::where('id', $shopId)
                ->where('platform', $platform)
                ->where('status', 1)
                ->first();

            if (!$shop) {
                return response()->json(['error' => 'Shop not found'], 404);
            }

            // 使用平台工厂获取服务实例并完成授权
            $service = PlatformServiceFactory::create($shop);
            $result = $service->authorize(['code' => $code]);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => '授权成功',
                    'shop_id' => $shop->id,
                ]);
            }

            return response()->json(['error' => 'Authorization failed'], 401);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OAuth callback error', [
                'platform' => $platform,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 验证state参数
     * 从缓存中取出state对应的shop_id并删除，防止重复使用
     *
     * @param string $state OAuth回调返回的state参数
     * @return int|null 对应的shop_id，验证失败返回null
     */
    protected function validateState(string $state): ?int
    {
        if (empty($state)) {
            return null;
        }

        $shopId = Cache::pull("oauth_state_{$state}");
        if (!$shopId) {
            \Illuminate\Support\Facades\Log::warning('OAuth state validation failed - state not found or expired', [
                'state' => $state,
            ]);
            return null;
        }

        return (int) $shopId;
    }
}