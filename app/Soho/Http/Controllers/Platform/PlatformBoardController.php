<?php

namespace App\Soho\Http\Controllers\Platform;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 平台铺货工作台（商品刊登 / 一键上架 / 上架任务 / 类目映射 / 平台属性）
 *
 * 数据源：
 * - soho_platform_products：平台商品（刊登与同步状态）
 * - soho_goods_publish_records：上架任务与执行结果
 * - soho_product_listing_templates：刊登模板
 * - soho_product_categories / soho_platform_products.platform_category_id：类目映射
 * - soho_product_attributes / soho_product_attribute_values：平台属性字典
 */
class PlatformBoardController extends Controller
{
    /** 商品刊登（平台商品列表） */
    public function listing(Request $request)
    {
        $rows = DB::table('soho_platform_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform', $p))
            ->when($request->input('status'), fn ($q, $st) => $q->where('platform_status', $st))
            ->when($request->input('shop_id'), fn ($q, $id) => $q->where('shop_id', $id))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(function ($qq) use ($kw) {
                $qq->where('title', 'like', "%{$kw}%")
                    ->orWhere('platform_product_id', 'like', "%{$kw}%")
                    ->orWhere('platform_sku', 'like', "%{$kw}%");
            }))
            ->orderByDesc('last_synced_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_platform_products')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'total' => (clone $base)->count(),
            'online' => (clone $base)->where('platform_status', 'online')->count(),
            'reviewing' => (clone $base)->where('platform_status', 'reviewing')->count(),
            'rejected' => (clone $base)->where('platform_status', 'rejected')->count(),
        ];

        $platforms = (clone $base)->select('platform')->distinct()->pluck('platform');
        $shops = $this->shops();

        return view('soho.platform.listing', compact('rows', 'stats', 'platforms', 'shops'));
    }

    /** 一键上架（选择商品 + 店铺，生成上架任务） */
    public function productPublish(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'product_ids' => ['required', 'array', 'min:1'],
                'product_ids.*' => ['integer'],
                'shop_id' => ['required', 'integer'],
                'platform' => ['required', 'string', 'max:20'],
            ], [], ['product_ids' => '商品', 'shop_id' => '店铺']);

            $created = 0;
            foreach ($data['product_ids'] as $productId) {
                $product = DB::table('soho_products')
                    ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
                    ->where('id', $productId)
                    ->first();
                if (! $product) {
                    continue;
                }

                DB::table('soho_goods_publish_records')->insert([
                    'system_goods_id' => $product->id,
                    'shop_id' => $data['shop_id'],
                    'platform' => $data['platform'],
                    'publish_status' => 'pending',
                    'operator_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }

            return redirect()->route('soho.platform.publish.task')
                ->with('success', "已提交 {$created} 个商品的上架任务，可在「上架任务」查看执行结果。");
        }

        $products = DB::table('soho_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('name')
            ->get(['id', 'product_code', 'name', 'retail_price']);

        $shops = $this->shops();
        $templates = DB::table('soho_product_listing_templates')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('status', 'active')
            ->get(['id', 'name', 'platform', 'shop_id']);

        return view('soho.platform.product-publish', compact('products', 'shops', 'templates'));
    }

    /** 上架任务（soho_goods_publish_records） */
    public function publishTask(Request $request)
    {
        $rows = DB::table('soho_goods_publish_records')
            ->when($request->input('platform'), fn ($q, $p) => $q->where('platform', $p))
            ->when($request->input('status'), fn ($q, $st) => $q->where('publish_status', $st))
            ->when($request->input('shop_id'), fn ($q, $id) => $q->where('shop_id', $id))
            ->when($request->input('search'), fn ($q, $kw) => $q->where('platform_goods_id', 'like', "%{$kw}%"))
            ->orderByRaw("FIELD(publish_status,'pending','publishing','failed','success')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $base = DB::table('soho_goods_publish_records');
        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->whereIn('publish_status', ['pending', 'publishing'])->count(),
            'success' => (clone $base)->where('publish_status', 'success')->count(),
            'failed' => (clone $base)->where('publish_status', 'failed')->count(),
        ];

        $shops = $this->shops();
        $products = DB::table('soho_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'name', 'product_code'])
            ->keyBy('id');
        $platforms = (clone $base)->select('platform')->distinct()->pluck('platform');

        return view('soho.platform.publish-task', compact('rows', 'stats', 'shops', 'products', 'platforms'));
    }

    /** 类目映射（本地分类 ↔ 平台类目 ID） */
    public function categoryMapping(Request $request)
    {
        $localCategories = DB::table('soho_product_categories')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderBy('sort_order')
            ->get(['id', 'name', 'parent_id', 'is_active']);

        $mapping = DB::table('soho_platform_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->whereNotNull('platform_category_id')
            ->select('platform', 'platform_category_id')
            ->selectRaw('COUNT(*) as product_count')
            ->selectRaw('COUNT(DISTINCT product_id) as spu_count')
            ->groupBy('platform', 'platform_category_id')
            ->orderBy('platform')
            ->get();

        $productCategory = DB::table('soho_products')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->whereNotNull('category_id')
            ->select('category_id')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id');

        $stats = [
            'categories' => $localCategories->count(),
            'mapped' => $mapping->sum('spu_count'),
            'platforms' => $mapping->pluck('platform')->unique()->count(),
            'unmapped' => DB::table('soho_products')
                ->when($this->tenantId(), fn ($q, $t) => $q->where('soho_products.tenant_id', $t))
                ->leftJoin('soho_platform_products as pp', 'pp.product_id', '=', 'soho_products.id')
                ->whereNull('pp.platform_category_id')
                ->count(),
        ];

        return view('soho.platform.category-mapping', compact('localCategories', 'mapping', 'productCategory', 'stats'));
    }

    /** 平台属性（属性字典 + 可选值） */
    public function attribute(Request $request)
    {
        $attributes = DB::table('soho_product_attributes')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('sort')
            ->get();

        $values = DB::table('soho_product_attribute_values')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->orderBy('sort_order')
            ->get(['id', 'product_attribute_id', 'value_name'])
            ->groupBy('product_attribute_id');

        $base = DB::table('soho_product_attributes')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));
        $stats = [
            'attributes' => (clone $base)->count(),
            'select' => (clone $base)->where('type', 'select')->count(),
            'input' => (clone $base)->where('type', 'input')->count(),
            'values' => DB::table('soho_product_attribute_values')->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))->count(),
        ];

        return view('soho.platform.attribute', compact('attributes', 'values', 'stats'));
    }

    // ---------------------------------------------------------------- 内部方法

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }

    private function shops()
    {
        return DB::table('soho_shops')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->get(['id', 'shop_name', 'platform'])
            ->keyBy('id');
    }
}
