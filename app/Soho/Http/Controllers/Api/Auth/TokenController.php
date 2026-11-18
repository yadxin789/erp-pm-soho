<?php

namespace App\Soho\Http\Controllers\Api\Auth;

use App\Soho\Models\Shop;
use App\Soho\Services\PlatformServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Token管理控制器
 */
class TokenController extends Controller
{
    /**
     * 刷新店铺的平台Token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|integer|exists:soho_shops,id',
        ]);

        $shop = Shop::findOrFail($request->shop_id);

        try {
            $service = PlatformServiceFactory::create($shop);
            $success = $service->refreshAccessToken();

            if ($success) {
                $shop->refresh();
                return response()->json([
                    'message' => 'Token刷新成功',
                    'data' => [
                        'shop_id' => $shop->id,
                        'token_expires_at' => $shop->token_expires_at,
                    ],
                ]);
            }

            return response()->json([
                'message' => 'Token刷新失败，请检查授权配置',
            ], 422);
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'shop_id' => $shop->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Token刷新异常: ' . $e->getMessage(),
            ], 500);
        }
    }
}