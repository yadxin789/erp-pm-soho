<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Product;
use App\Soho\Models\ProductSku;
use App\Soho\Models\ProductCategory;
use App\Soho\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * 商品 SPU 列表
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand', 'skus'])
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('product_code', 'like', "%{$request->search}%");
            })
            ->when($request->category_id, function($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->brand_id, function($query) use ($request) {
                $query->where('brand_id', $request->brand_id);
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('product.index', compact('products'));
    }

    /**
     * 创建商品 SPU
     */
    public function create()
    {
        $categories = ProductCategory::all();
        $brands = Brand::all();
        return view('product.create', compact('categories', 'brands'));
    }

    /**
     * 保存新商品 SPU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|max:100|unique:soho_products',
            'subtitle' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:soho_product_categories,id',
            'brand_id' => 'nullable|exists:soho_brands,id',
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending,active,inactive,archived,error',
            'skus' => 'nullable|array',
        ]);

        if (! empty($validated['attributes']) && is_string($validated['attributes'])) {
            $decoded = json_decode($validated['attributes'], true);
            $validated['attributes'] = is_array($decoded) ? $decoded : null;
        }

        $skus = $validated['skus'] ?? [];
        unset($validated['skus']);

        // 设置租户ID
        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;

        DB::transaction(function() use ($validated, $skus) {
            $product = Product::create($validated);

            // 创建 SKU
            foreach ($skus as $skuData) {
                if (!empty($skuData['sku_code'])) {
                    ProductSku::create([
                        'tenant_id' => $validated['tenant_id'],
                        'product_id' => $product->id,
                        'sku_code' => $skuData['sku_code'],
                        'name' => $skuData['name'] ?? $skuData['sku_code'],
                        'specification' => !empty($skuData['specification']) ? json_encode($skuData['specification']) : null,
                        'cost_price' => $skuData['cost_price'] ?? 0,
                        'sale_price' => $skuData['sale_price'] ?? 0,
                        'status' => $skuData['status'] ?? 'active',
                    ]);
                }
            }
        });

        return redirect()->route('soho.product.index')->with('success', '商品创建成功');
    }

    /**
     * 商品 SPU 详情
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'skus']);
        return view('product.show', compact('product'));
    }

    /**
     * 编辑商品 SPU
     */
    public function edit(Product $product)
    {
        $product->load(['skus']);
        $categories = ProductCategory::all();
        $brands = Brand::all();
        return view('product.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * 更新商品 SPU
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|max:100|unique:soho_products,product_code,' . $product->id,
            'subtitle' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:soho_product_categories,id',
            'brand_id' => 'nullable|exists:soho_brands,id',
            'cost_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending,active,inactive,archived,error',
            'skus' => 'nullable|array',
        ]);

        if (! empty($validated['attributes']) && is_string($validated['attributes'])) {
            $decoded = json_decode($validated['attributes'], true);
            $validated['attributes'] = is_array($decoded) ? $decoded : null;
        }

        $skus = $validated['skus'] ?? [];
        unset($validated['skus']);

        DB::transaction(function() use ($product, $validated, $skus) {
            $product->update($validated);

            // 同步 SKU
            $existingIds = $product->skus->pluck('id')->toArray();
            $updatedIds = [];

            foreach ($skus as $skuData) {
                if (empty($skuData['sku_code'])) {
                    continue;
                }

                if (!empty($skuData['id'])) {
                    // 更新已有 SKU
                    $sku = ProductSku::find($skuData['id']);
                    if ($sku) {
                        $sku->update([
                            'sku_code' => $skuData['sku_code'],
                            'name' => $skuData['name'] ?? $skuData['sku_code'],
                            'specification' => !empty($skuData['specification']) ? json_encode($skuData['specification']) : null,
                            'cost_price' => $skuData['cost_price'] ?? 0,
                            'sale_price' => $skuData['sale_price'] ?? 0,
                            'status' => $skuData['status'] ?? 'active',
                        ]);
                        $updatedIds[] = $sku->id;
                    }
                } else {
                    // 创建新 SKU
                    $sku = ProductSku::create([
                        'tenant_id' => $product->tenant_id,
                        'product_id' => $product->id,
                        'sku_code' => $skuData['sku_code'],
                        'name' => $skuData['name'] ?? $skuData['sku_code'],
                        'specification' => !empty($skuData['specification']) ? json_encode($skuData['specification']) : null,
                        'cost_price' => $skuData['cost_price'] ?? 0,
                        'sale_price' => $skuData['sale_price'] ?? 0,
                        'status' => $skuData['status'] ?? 'active',
                    ]);
                    $updatedIds[] = $sku->id;
                }
            }

            // 删除不在提交列表中的 SKU
            $toDelete = array_diff($existingIds, $updatedIds);
            ProductSku::whereIn('id', $toDelete)->delete();
        });

        return redirect()->route('soho.product.index')->with('success', '商品更新成功');
    }

    /**
     * 删除商品 SPU
     */
    public function destroy(Product $product)
    {
        // 检查是否有关联的 SKU 数据
        if ($product->skus()->count() > 0) {
            $product->skus()->delete();
        }

        $product->delete();
        return redirect()->route('soho.product.index')->with('success', '商品删除成功');
    }
}
