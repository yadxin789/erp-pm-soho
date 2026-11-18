<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\PlatformSku;
use App\Soho\Models\Shop;
use App\Soho\Models\SystemGoods;
use Illuminate\Http\Request;

class PlatformSkuController extends Controller
{
    /**
     * SKU 映射列表
     */
    public function index(Request $request)
    {
        $skuMappings = PlatformSku::with(['shop', 'systemGoods'])
            ->when($request->search, function($query) use ($request) {
                $query->where('platform_sku', 'like', "%{$request->search}%")
                    ->orWhere('platform_goods_id', 'like', "%{$request->search}%");
            })
            ->when($request->shop_id, function($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            })
            ->when($request->system_goods_id, function($query) use ($request) {
                $query->where('system_goods_id', $request->system_goods_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $shops = Shop::where('status', true)->orderBy('shop_name')->get();

        return view('platform-sku.index', compact('skuMappings', 'shops'));
    }

    /**
     * 创建 SKU 映射
     */
    public function create()
    {
        $shops = Shop::where('status', true)->orderBy('shop_name')->get();
        $systemGoods = SystemGoods::where('status', true)->orderBy('goods_name')->get();

        return view('platform-sku.create', compact('shops', 'systemGoods'));
    }

    /**
     * 保存 SKU 映射
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'system_goods_id' => 'required|exists:soho_system_goods,id',
            'shop_id' => 'required|exists:soho_shops,id',
            'platform_sku' => 'required|string|max:200',
            'platform_goods_id' => 'nullable|string|max:200',
            'platform_goods_url' => 'nullable|string|max:500',
            'platform_price' => 'nullable|numeric|min:0',
            'platform_stock' => 'nullable|integer|min:0',
            'stock_sync_enabled' => 'boolean',
        ]);

        $validated['stock_sync_enabled'] = $validated['stock_sync_enabled'] ?? true;

        PlatformSku::create($validated);

        return redirect()->route('soho.platform-sku.index')->with('success', 'SKU 映射创建成功');
    }

    /**
     * 编辑 SKU 映射
     */
    public function edit(PlatformSku $platformSku)
    {
        $shops = Shop::where('status', true)->orderBy('shop_name')->get();
        $systemGoods = SystemGoods::where('status', true)->orderBy('goods_name')->get();

        return view('platform-sku.edit', compact('platformSku', 'shops', 'systemGoods'));
    }

    /**
     * 更新 SKU 映射
     */
    public function update(Request $request, PlatformSku $platformSku)
    {
        $validated = $request->validate([
            'system_goods_id' => 'required|exists:soho_system_goods,id',
            'shop_id' => 'required|exists:soho_shops,id',
            'platform_sku' => 'required|string|max:200',
            'platform_goods_id' => 'nullable|string|max:200',
            'platform_goods_url' => 'nullable|string|max:500',
            'platform_price' => 'nullable|numeric|min:0',
            'platform_stock' => 'nullable|integer|min:0',
            'stock_sync_enabled' => 'boolean',
        ]);

        $platformSku->update($validated);

        return redirect()->route('soho.platform-sku.index')->with('success', 'SKU 映射更新成功');
    }

    /**
     * 删除 SKU 映射
     */
    public function destroy(PlatformSku $platformSku)
    {
        $platformSku->delete();
        return redirect()->route('soho.platform-sku.index')->with('success', 'SKU 映射删除成功');
    }

    /**
     * 批量导入 SKU 映射
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt',
        ]);

        $filePath = $request->file('import_file')->getRealPath();
        $content = file_get_contents($filePath);
        $content = ltrim($content, "\xEF\xBB\xBF");
        $lines = array_filter(explode("\n", str_replace("\r\n", "\n", $content)));

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $parts = str_getcsv($line);
            if (count($parts) < 3) {
                $failCount++;
                $errors[] = "格式错误: {$line}";
                continue;
            }

            $shopId = (int) trim($parts[0]);
            $systemGoodsId = (int) trim($parts[1]);
            $platformSku = trim($parts[2]);
            $platformPrice = isset($parts[3]) ? (float) trim($parts[3]) : null;
            $platformStock = isset($parts[4]) ? (int) trim($parts[4]) : null;

            $shop = Shop::find($shopId);
            if (!$shop) {
                $failCount++;
                $errors[] = "店铺不存在: ID={$shopId}";
                continue;
            }

            $systemGoods = SystemGoods::find($systemGoodsId);
            if (!$systemGoods) {
                $failCount++;
                $errors[] = "系统商品不存在: ID={$systemGoodsId}";
                continue;
            }

            // 检查是否已存在
            $existing = PlatformSku::where('system_goods_id', $systemGoodsId)
                ->where('shop_id', $shopId)
                ->first();

            if ($existing) {
                $existing->update([
                    'platform_sku' => $platformSku,
                    'platform_price' => $platformPrice,
                    'platform_stock' => $platformStock,
                ]);
            } else {
                PlatformSku::create([
                    'system_goods_id' => $systemGoodsId,
                    'shop_id' => $shopId,
                    'platform_sku' => $platformSku,
                    'platform_price' => $platformPrice,
                    'platform_stock' => $platformStock,
                    'stock_sync_enabled' => true,
                ]);
            }

            $successCount++;
        }

        $message = "导入完成：成功 {$successCount} 条";
        if ($failCount > 0) {
            $message .= "，失败 {$failCount} 条";
        }

        return back()->with($failCount > 0 ? 'warning' : 'success', $message);
    }
}
