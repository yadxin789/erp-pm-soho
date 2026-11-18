<?php

namespace App\Soho\Http\Controllers\Price;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\PriceRule;
use App\Soho\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 价格计算控制器
 * 提供价格计算功能，支持选择商品、自动匹配适用的价格规则并计算建议价格
 */
class PriceCalculateController extends Controller
{
    /**
     * 价格计算页面
     * 支持选择商品后获取适用的价格规则，计算建议价格
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
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        $matchedRules = [];
        $calculatedPrice = null;
        $priceBreakdown = [];

        if ($productId = $request->input('product_id')) {
            $selectedProduct = Product::with(['category', 'skus'])
                ->where('tenant_id', $tenantId)
                ->find($productId);

            if ($selectedProduct) {
                $matchedRules = $this->matchRules($selectedProduct, $tenantId);
                $calculatedPrice = $this->calculatePrice($selectedProduct, $matchedRules, $priceBreakdown);
            }
        }

        $allRules = PriceRule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get();

        $typeLabels = PriceRule::getTypeLabels();

        return view('price.price-calculate.index', compact(
            'products',
            'selectedProduct',
            'matchedRules',
            'calculatedPrice',
            'priceBreakdown',
            'allRules',
            'typeLabels',
            'search'
        ));
    }

    /**
     * 根据商品匹配适用的价格规则
     *
     * @param Product $product
     * @param int|null $tenantId
     * @return \Illuminate\Support\Collection
     */
    private function matchRules(Product $product, ?int $tenantId)
    {
        return PriceRule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($product) {
                $query->whereNull('min_price')
                      ->orWhere('min_price', '<=', $product->retail_price ?? 0);
            })
            ->where(function ($query) use ($product) {
                $query->whereNull('max_price')
                      ->orWhere('max_price', '>=', $product->retail_price ?? 0);
            })
            ->orderByDesc('priority')
            ->get();
    }

    /**
     * 根据匹配的规则计算建议价格
     *
     * @param Product $product
     * @param \Illuminate\Support\Collection $rules
     * @param array $breakdown
     * @return float|null
     */
    private function calculatePrice(Product $product, $rules, array &$breakdown): ?float
    {
        $basePrice = $product->retail_price ?? $product->cost_price ?? 0;
        if ($basePrice <= 0) {
            return null;
        }

        $breakdown = [
            ['step' => '基础价格', 'price' => $basePrice, 'description' => '商品当前零售价格'],
        ];

        $currentPrice = $basePrice;

        foreach ($rules as $rule) {
            $parameters = $rule->parameters ?? [];

            switch ($rule->type) {
                case 'markup':
                    $percentage = $parameters['percentage'] ?? 0;
                    $currentPrice = $currentPrice * (1 + $percentage / 100);
                    $breakdown[] = [
                        'step' => $rule->name . '（加价 ' . $percentage . '%）',
                        'price' => $currentPrice,
                        'description' => $rule->description,
                    ];
                    break;

                case 'discount':
                    $percentage = $parameters['percentage'] ?? 0;
                    $currentPrice = $currentPrice * (1 - $percentage / 100);
                    $breakdown[] = [
                        'step' => $rule->name . '（折扣 ' . $percentage . '%）',
                        'price' => $currentPrice,
                        'description' => $rule->description,
                    ];
                    break;

                case 'fixed':
                    $fixedPrice = $parameters['price'] ?? $currentPrice;
                    $currentPrice = $fixedPrice;
                    $breakdown[] = [
                        'step' => $rule->name . '（固定价格）',
                        'price' => $currentPrice,
                        'description' => $rule->description,
                    ];
                    break;

                case 'cost_plus':
                    $costPercentage = $parameters['cost_percentage'] ?? 0;
                    $costBasis = $product->cost_price ?? $basePrice;
                    $currentPrice = $costBasis * (1 + $costPercentage / 100);
                    $breakdown[] = [
                        'step' => $rule->name . '（成本加成 ' . $costPercentage . '%）',
                        'price' => $currentPrice,
                        'description' => $rule->description,
                    ];
                    break;

                case 'range':
                    $rangeMin = $parameters['min'] ?? $rule->min_price;
                    $rangeMax = $parameters['max'] ?? $rule->max_price;
                    if ($rangeMin && $rangeMax) {
                        $currentPrice = max($rangeMin, min($rangeMax, $currentPrice));
                        $breakdown[] = [
                            'step' => $rule->name . '（区间定价）',
                            'price' => $currentPrice,
                            'description' => '价格限制在区间 ' . $rangeMin . ' - ' . $rangeMax,
                        ];
                    }
                    break;

                case 'tiered':
                    $tiers = $parameters['tiers'] ?? [];
                    foreach ($tiers as $tier) {
                        $maxQty = $tier['max_quantity'] ?? PHP_FLOAT_MAX;
                        if ($basePrice <= $maxQty) {
                            $tierPrice = $tier['price'] ?? $currentPrice;
                            $currentPrice = $tierPrice;
                            $breakdown[] = [
                                'step' => $rule->name . '（阶梯定价）',
                                'price' => $currentPrice,
                                'description' => $rule->description,
                            ];
                            break;
                        }
                    }
                    break;

                case 'competitor':
                    $adjustment = $parameters['adjustment'] ?? 0;
                    $currentPrice = $currentPrice + $adjustment;
                    $breakdown[] = [
                        'step' => $rule->name . '（竞品跟随）',
                        'price' => $currentPrice,
                        'description' => $rule->description,
                    ];
                    break;
            }
        }

        return round($currentPrice, 2);
    }
}