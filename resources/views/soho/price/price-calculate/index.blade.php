@extends('soho::layouts.app')

@section('title', '价格计算器')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">价格中心</a></li>
        <li class="breadcrumb-item active">价格计算器</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-calculator me-2"></i>价格计算器</h4>
</div>

<div class="row">
    {{-- 左侧：商品选择和参数调整 --}}
    <div class="col-lg-6">
        {{-- 商品选择 --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-box me-2"></i>选择商品</h6></div>
            <div class="card-body">
                <form method="GET" action="{{ route('soho.price.calculate.index') }}" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">商品</label>
                        <select class="form-select" name="product_id" id="productSelect">
                            <option value="">请选择商品</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->product_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search me-1"></i>加载商品
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 商品信息 --}}
        @if(isset($selectedProduct))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-info-circle me-2"></i>商品信息</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th width="120">商品名称</th>
                        <td>{{ $selectedProduct->name }}</td>
                    </tr>
                    <tr>
                        <th>商品编码</th>
                        <td><code>{{ $selectedProduct->product_code }}</code></td>
                    </tr>
                    <tr>
                        <th>成本价</th>
                        <td class="fw-semibold">¥{{ number_format($selectedProduct->cost_price ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>当前售价</th>
                        <td class="fw-semibold text-primary">¥{{ number_format($selectedProduct->current_price ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>分类</th>
                        <td>{{ $selectedProduct->category->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- 手动调整参数 --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-sliders me-2"></i>手动调整参数</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">成本价 (¥)</label>
                        <input type="number" step="0.01" class="form-control" id="manualCostPrice" value="{{ $selectedProduct->cost_price ?? 0 }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">加价比例 (%)</label>
                        <input type="number" step="0.01" class="form-control" id="manualMarkup" value="30">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">固定加价金额 (¥)</label>
                        <input type="number" step="0.01" class="form-control" id="manualMarkupAmount" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">最低售价 (¥)</label>
                        <input type="number" step="0.01" class="form-control" id="manualMinPrice" value="0">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" onclick="recalculatePrice()">
                    <i class="fa fa-refresh me-1"></i>重新计算
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- 右侧：匹配规则和计算结果 --}}
    <div class="col-lg-6">
        {{-- 匹配的规则 --}}
        @if(isset($matchedRules) && count($matchedRules) > 0)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-check-circle me-2"></i>匹配的规则</h6></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($matchedRules as $rule)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $rule->name }}</div>
                                <small class="text-muted">{{ $typeLabels[$rule->type] ?? $rule->type }} · 优先级 {{ $rule->priority ?? 0 }}</small>
                            </div>
                            <span class="badge bg-info">{{ $rule->markup_percent ?? 0 }}% + ¥{{ $rule->markup_amount ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- 价格分解步骤 --}}
        @if(isset($selectedProduct))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-sitemap me-2"></i>价格分解步骤</h6></div>
            <div class="card-body">
                <div class="timeline-step mb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">1</div>
                        <div class="ms-3">
                            <div class="text-muted small">成本价</div>
                            <div class="fw-semibold">¥{{ number_format($selectedProduct->cost_price ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mb-3">
                    <i class="fa fa-arrow-down text-muted"></i>
                </div>
                <div class="timeline-step mb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">2</div>
                        <div class="ms-3">
                            <div class="text-muted small">应用规则加价 <span id="ruleMarkupLabel">+ 30%</span></div>
                            <div class="fw-semibold" id="ruleMarkupValue">+ ¥{{ number_format(($selectedProduct->cost_price ?? 0) * 0.3, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mb-3">
                    <i class="fa fa-arrow-down text-muted"></i>
                </div>
                <div class="timeline-step mb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">3</div>
                        <div class="ms-3">
                            <div class="text-muted small">建议售价</div>
                            <div class="fw-bold text-primary" id="suggestedPrice" style="font-size: 1.5rem;">¥{{ number_format(($selectedProduct->cost_price ?? 0) * 1.3, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 计算结果汇总 --}}
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-bar-chart me-2"></i>计算结果汇总</h6></div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted small">成本价</div>
                        <div class="h5 text-muted mb-0" id="summaryCost">¥{{ number_format($selectedProduct->cost_price ?? 0, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">建议售价</div>
                        <div class="h5 text-primary mb-0" id="summarySuggested">¥{{ number_format(($selectedProduct->cost_price ?? 0) * 1.3, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">预计利润</div>
                        <div class="h5 text-success mb-0" id="summaryProfit">¥{{ number_format(($selectedProduct->cost_price ?? 0) * 0.3, 2) }}</div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">利润率</span>
                    <span class="fw-semibold" id="summaryProfitRate">30.00%</span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted">与当前价格差异</span>
                    <span class="fw-semibold" id="summaryDiff">¥{{ number_format((($selectedProduct->cost_price ?? 0) * 1.3) - ($selectedProduct->current_price ?? 0), 2) }}</span>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fa fa-hand-pointer-o fa-2x mb-2"></i>
                <div>请先选择商品以查看匹配的规则和计算结果</div>
            </div>
        </div>
        @endif
    </div>
</div>

@if(isset($selectedProduct))
<script>
function recalculatePrice() {
    var costPrice = parseFloat(document.getElementById('manualCostPrice').value) || 0;
    var markupPercent = parseFloat(document.getElementById('manualMarkup').value) || 0;
    var markupAmount = parseFloat(document.getElementById('manualMarkupAmount').value) || 0;
    var minPrice = parseFloat(document.getElementById('manualMinPrice').value) || 0;

    var markupValue = costPrice * (markupPercent / 100) + markupAmount;
    var suggestedPrice = costPrice + markupValue;

    if (suggestedPrice < minPrice) {
        suggestedPrice = minPrice;
    }

    var profit = suggestedPrice - costPrice;
    var profitRate = costPrice > 0 ? (profit / costPrice) * 100 : 0;

    document.getElementById('ruleMarkupLabel').textContent = '+ ' + markupPercent + '% + ¥' + markupAmount.toFixed(2);
    document.getElementById('ruleMarkupValue').textContent = '+ ¥' + markupValue.toFixed(2);
    document.getElementById('suggestedPrice').textContent = '¥' + suggestedPrice.toFixed(2);

    document.getElementById('summaryCost').textContent = '¥' + costPrice.toFixed(2);
    document.getElementById('summarySuggested').textContent = '¥' + suggestedPrice.toFixed(2);
    document.getElementById('summaryProfit').textContent = '¥' + profit.toFixed(2);
    document.getElementById('summaryProfitRate').textContent = profitRate.toFixed(2) + '%';

    var currentPrice = parseFloat('{{ $selectedProduct->current_price ?? 0 }}');
    var diff = suggestedPrice - currentPrice;
    var diffText = (diff >= 0 ? '+' : '') + '¥' + diff.toFixed(2);
    document.getElementById('summaryDiff').textContent = diffText;
    document.getElementById('summaryDiff').className = 'fw-semibold ' + (diff >= 0 ? 'text-success' : 'text-danger');
}
</script>
@endif
@endsection