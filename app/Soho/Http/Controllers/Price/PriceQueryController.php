<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\Product;
use App\Soho\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 价格查询控制器
 * 提供商品价格的查询与统计功能，支持按商品、SKU、平台、价格范围等条件筛选
 */
class PriceQueryController extends Controller
{
    /**
     * 价格查询页面
     * 支持按商品名称、SKU编码、分类、价格区间等条件筛选，返回查询结果和统计数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $products = Product::with(['category', 'skus'])
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search = $request->input('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->when($request->input('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->input('platform'), function ($query) use ($request) {
                $query->whereHas('platformProducts', function ($q) use ($request) {
                    $q->where('platform', $request->input('platform'));
                });
            })
            ->when($minPrice = $request->input('min_price'), function ($query) use ($minPrice) {
                $query->where('retail_price', '>=', $minPrice);
            })
            ->when($maxPrice = $request->input('max_price'), function ($query) use ($maxPrice) {
                $query->where('retail_price', '<=', $maxPrice);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $statistics = $this->getStatistics($tenantId, $request);

        $categories = ProductCategory::where('tenant_id', $tenantId)->get();

        return view('price.price-query.index', compact(
            'products',
            'statistics',
            'categories',
            'search'
        ));
    }

    /**
     * 获取价格统计数据
     *
     * @param int|null $tenantId
     * @param Request $request
     * @return array
     */
    private function getStatistics(?int $tenantId, Request $request): array
    {
        $query = Product::when($tenantId, function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        $totalProducts = (clone $query)->count();

        $priceStats = (clone $query)
            ->selectRaw('COUNT(*) as total, AVG(retail_price) as avg_price, MIN(retail_price) as min_price, MAX(retail_price) as max_price')
            ->first();

        $lowPriceCount = (clone $query)->where('retail_price', '<', 10)->count();
        $highPriceCount = (clone $query)->where('retail_price', '>', 1000)->count();

        $categoryDistribution = (clone $query)
            ->selectRaw('category_id, COUNT(*) as count')
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();

        return [
            'total_products' => $totalProducts,
            'avg_price' => $priceStats->avg_price ?? 0,
            'min_price' => $priceStats->min_price ?? 0,
            'max_price' => $priceStats->max_price ?? 0,
            'low_price_count' => $lowPriceCount,
            'high_price_count' => $highPriceCount,
            'category_distribution' => $categoryDistribution,
        ];
    }
}