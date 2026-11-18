<?php

namespace App\Soho\Http\Controllers\Api\Product;

use App\Soho\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * 商品API控制器
 */
class ProductController extends Controller
{
    /**
     * 获取商品列表
     */
    public function index(Request $request)
    {
        $products = Product::query();

        if ($request->has('status')) {
            $products->where('status', $request->status);
        }
        if ($request->has('category_id')) {
            $products->where('category_id', $request->category_id);
        }

        return response()->json([
            'data' => $products->paginate($request->per_page ?? 20),
        ]);
    }

    /**
     * 获取商品详情
     */
    public function show(Product $product)
    {
        return response()->json([
            'data' => $product->load('skus', 'images'),
        ]);
    }

    /**
     * 创建商品
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:50|unique:soho_products,product_code',
            'name' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'brand_id' => 'nullable|integer|exists:soho_brands,id',
            'category_id' => 'nullable|integer|exists:soho_product_categories,id',
            'status' => 'boolean',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id ?? 1;
        $validated['created_by'] = auth()->id();
        $product = Product::create($validated);

        return response()->json([
            'data' => $product,
            'message' => '商品创建成功',
        ], 201);
    }

    /**
     * 更新商品
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_code' => 'sometimes|string|max:50|unique:soho_products,product_code,' . $product->id,
            'name' => 'sometimes|string|max:200',
            'subtitle' => 'sometimes|nullable|string|max:500',
            'brand_id' => 'sometimes|nullable|integer|exists:soho_brands,id',
            'category_id' => 'sometimes|nullable|integer|exists:soho_product_categories,id',
            'status' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string',
            'cost_price' => 'sometimes|numeric|min:0',
            'retail_price' => 'sometimes|numeric|min:0',
            'weight' => 'sometimes|nullable|numeric|min:0',
            'attributes' => 'sometimes|nullable|array',
        ]);

        $product->update($validated);

        return response()->json([
            'data' => $product,
            'message' => '商品更新成功',
        ]);
    }

    /**
     * 删除商品
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => '商品删除成功',
        ]);
    }

    /**
     * 获取商品SKU列表
     */
    public function skus(Product $product)
    {
        return response()->json([
            'data' => $product->skus,
        ]);
    }

    /**
     * 发布商品到平台
     */
    public function publish(Request $request, Product $product)
    {
        $request->validate([
            'shop_id' => 'required|integer',
        ]);

        // TODO: 创建发布任务并异步执行

        return response()->json([
            'data' => $product,
            'message' => '发布任务已创建',
        ], 202);
    }
}