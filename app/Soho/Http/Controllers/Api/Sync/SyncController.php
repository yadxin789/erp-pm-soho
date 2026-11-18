<?php

namespace App\Soho\Http\Controllers\Api\Sync;

use App\Soho\Models\SyncTaskLog;
use App\Soho\Services\OrderSyncService;
use App\Soho\Services\StockSyncService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 同步任务API控制器
 */
class SyncController extends Controller
{
    /**
     * 同步订单
     */
    public function orders(Request $request)
    {
        $request->validate([
            'shop_id' => 'integer|exists:soho_shops,id',
        ]);

        $service = app(OrderSyncService::class);

        if ($request->has('shop_id')) {
            $shop = \App\Soho\Models\Shop::findOrFail($request->shop_id);
            $result = $service->syncShopOrders($shop);
        } else {
            $result = $service->syncAll();
        }

        return response()->json([
            'data' => $result,
            'message' => '订单同步任务已启动',
        ]);
    }

    /**
     * 同步商品
     */
    public function products(Request $request)
    {
        $request->validate([
            'shop_id' => 'integer|exists:soho_shops,id',
        ]);

        $shops = $request->has('shop_id')
            ? \App\Soho\Models\Shop::where('id', $request->shop_id)->where('status', 1)->get()
            : \App\Soho\Models\Shop::where('status', 1)->where('auth_status', 1)->where('sync_enabled', 1)->get();

        $results = [];
        foreach ($shops as $shop) {
            $taskLog = SyncTaskLog::create([
                'task_name' => "{$shop->platform_label} - {$shop->shop_name} 商品同步",
                'task_type' => 'product_sync',
                'platform' => $shop->platform,
                'shop_id' => $shop->id,
                'status' => 'running',
                'started_at' => now(),
            ]);

            try {
                $service = \App\Soho\Services\PlatformServiceFactory::create($shop);
                $products = $service->fetchProducts(['page_size' => 50]);

                $taskLog->update([
                    'status' => 'success',
                    'total_count' => count($products),
                    'success_count' => count($products),
                    'finished_at' => now(),
                ]);

                $results[$shop->id] = ['success' => true, 'synced' => count($products)];
            } catch (\Exception $e) {
                $taskLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'finished_at' => now(),
                ]);

                $results[$shop->id] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'data' => $results,
            'message' => '商品同步任务已启动',
        ]);
    }

    /**
     * 同步库存
     */
    public function inventory(Request $request)
    {
        $request->validate([
            'shop_id' => 'integer|exists:soho_shops,id',
        ]);

        $service = app(StockSyncService::class);

        if ($request->has('shop_id')) {
            $shop = \App\Soho\Models\Shop::findOrFail($request->shop_id);
            $result = $service->syncShopStock($shop);
        } else {
            $result = $service->syncAll();
        }

        return response()->json([
            'data' => $result,
            'message' => '库存同步任务已启动',
        ]);
    }

    /**
     * 获取同步任务列表
     */
    public function tasks(Request $request)
    {
        $tasks = SyncTaskLog::query()
            ->when($request->task_type, fn($q, $type) => $q->where('task_type', $type))
            ->when($request->platform, fn($q, $p) => $q->where('platform', $p))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->with('shop')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $tasks,
        ]);
    }

    /**
     * 获取同步任务详情
     */
    public function task($task)
    {
        $taskLog = SyncTaskLog::with('shop')->findOrFail($task);

        return response()->json([
            'data' => $taskLog,
        ]);
    }
}
