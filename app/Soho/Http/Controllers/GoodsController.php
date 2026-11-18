<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\SystemGoods;
use App\Soho\Models\GoodsPublishRecord;
use App\Soho\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoodsController extends Controller
{
    /**
     * 商品列表
     */
    public function index(Request $request)
    {
        $goods = SystemGoods::query()
            ->when($request->search, function($query) use ($request) {
                $query->where('goods_name', 'like', "%{$request->search}%")
                    ->orWhere('system_sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            })
            ->when($request->brand, function($query) use ($request) {
                $query->where('brand', $request->brand);
            })
            ->when($request->category, function($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->when($request->status !== null && $request->status !== '', function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('goods.index', compact('goods'));
    }

    /**
     * 创建商品
     */
    public function create()
    {
        return view('goods.create');
    }

    /**
     * 保存新商品
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'goods_name' => 'required|string|max:300',
            'system_sku' => 'required|string|max:100|unique:soho_system_goods',
            'barcode' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
        ]);

        $validated['status'] = $validated['status'] ?? true;
        
        SystemGoods::create($validated);

        return redirect()->route('soho.goods.index')->with('success', '商品创建成功');
    }

    /**
     * 编辑商品
     */
    public function edit(SystemGoods $goods)
    {
        return view('goods.edit', compact('goods'));
    }

    /**
     * 更新商品
     */
    public function update(Request $request, SystemGoods $goods)
    {
        $validated = $request->validate([
            'goods_name' => 'required|string|max:300',
            'system_sku' => 'required|string|max:100|unique:soho_system_goods,system_sku,' . $goods->id,
            'barcode' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:20',
        ]);

        $goods->update($validated);

        return redirect()->route('soho.goods.index')->with('success', '商品更新成功');
    }

    /**
     * 删除商品
     */
    public function destroy(SystemGoods $goods)
    {
        if ($goods->stockSyncLogs()->count() > 0) {
            return back()->with('error', '该商品有同步记录，无法删除');
        }

        $goods->delete();
        return redirect()->route('soho.goods.index')->with('success', '商品删除成功');
    }

    /**
     * 批量发布 - 将商品发布到指定平台店铺
     */
    public function batchPublish(Request $request)
    {
        $request->validate([
            'goods_ids' => 'required|array',
            'goods_ids.*' => 'exists:soho_system_goods,id',
            'shop_ids' => 'required|array',
            'shop_ids.*' => 'exists:soho_shops,id',
        ]);

        $goodsIds = $request->goods_ids;
        $shopIds = $request->shop_ids;

        $goodsList = SystemGoods::whereIn('id', $goodsIds)->get();
        $shops = Shop::whereIn('id', $shopIds)->where('status', true)->get();

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($goodsList as $goods) {
            foreach ($shops as $shop) {
                try {
                    // 检查是否已发布
                    $existing = GoodsPublishRecord::where('system_goods_id', $goods->id)
                        ->where('shop_id', $shop->id)
                        ->where('platform', $shop->platform)
                        ->where('status', 'published')
                        ->first();

                    if ($existing) {
                        $failCount++;
                        $errors[] = "\"{$goods->name}\" 已发布到店铺 \"{$shop->name}\"";
                        continue;
                    }

                    // 创建发布记录
                    GoodsPublishRecord::create([
                        'system_goods_id' => $goods->id,
                        'platform' => $shop->platform,
                        'shop_id' => $shop->id,
                        'status' => 'pending',
                        'published_at' => now(),
                    ]);

                    $successCount++;
                    Log::info("商品发布记录创建: {$goods->system_sku} → {$shop->name}");

                } catch (\Exception $e) {
                    $failCount++;
                    $errors[] = "\"{$goods->name}\" → \"{$shop->name}\": " . $e->getMessage();
                    Log::error("商品发布失败: {$e->getMessage()}");
                }
            }
        }

        $message = "批量发布完成：成功 {$successCount} 条，失败 {$failCount} 条";
        if (!empty($errors)) {
            $message .= "\n详情: " . implode(" | ", array_slice($errors, 0, 5));
        }

        return back()->with($failCount > 0 ? 'warning' : 'success', $message);
    }
}