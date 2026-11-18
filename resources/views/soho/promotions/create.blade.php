@extends('soho::layouts.app')

@section('title', '创建促销活动')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">创建促销活动</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('promotions.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <!-- 活动名称 -->
                    <div class="mb-3">
                        <label class="form-label">活动名称 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="请输入活动名称">
                    </div>
                    <!-- 活动类型 -->
                    <div class="mb-3">
                        <label class="form-label">活动类型 <span class="text-danger">*</span></label>
                        <select name="type" id="promotionType" class="form-select" required>
                            <option value="">请选择类型</option>
                            <option value="full_reduction" @selected(old('type') == 'full_reduction')>满减促销</option>
                            <option value="discount" @selected(old('type') == 'discount')>折扣促销</option>
                            <option value="buy_gift" @selected(old('type') == 'buy_gift')>买赠促销</option>
                            <option value="flash_sale" @selected(old('type') == 'flash_sale')>限时秒杀</option>
                        </select>
                    </div>
                    <!-- 开始时间 -->
                    <div class="mb-3">
                        <label class="form-label">开始时间 <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time') }}" required>
                    </div>
                    <!-- 结束时间 -->
                    <div class="mb-3">
                        <label class="form-label">结束时间 <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_time" class="form-control" value="{{ old('end_time') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- 促销规则 -->
                    <div class="mb-3">
                        <label class="form-label">促销规则</label>
                        <div id="ruleConfig">
                            <!-- 满减规则 -->
                            <div class="rule-item" data-type="full_reduction" style="display:none;">
                                <div class="row g-2 mb-2">
                                    <div class="col-md-5">
                                        <label class="form-label">满</label>
                                        <input type="number" step="0.01" name="rules[full_reduction][threshold]" class="form-control" value="{{ old('rules.full_reduction.threshold') }}" placeholder="金额阈值">
                                    </div>
                                    <div class="col-md-2 text-center align-self-end">达到</div>
                                    <div class="col-md-5">
                                        <label class="form-label">减</label>
                                        <input type="number" step="0.01" name="rules[full_reduction][reduction]" class="form-control" value="{{ old('rules.full_reduction.reduction') }}" placeholder="减免金额">
                                    </div>
                                </div>
                            </div>
                            <!-- 折扣规则 -->
                            <div class="rule-item" data-type="discount" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label">折扣</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" min="0" max="10" name="rules[discount][value]" class="form-control" value="{{ old('rules.discount.value') }}" placeholder="如 8 表示 8折">
                                        <span class="input-group-text">折</span>
                                    </div>
                                </div>
                            </div>
                            <!-- 买赠规则 -->
                            <div class="rule-item" data-type="buy_gift" style="display:none;">
                                <div class="row g-2 mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">购买数量</label>
                                        <input type="number" name="rules[buy_gift][buy_qty]" class="form-control" value="{{ old('rules.buy_gift.buy_qty') }}" placeholder="购买件数">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">赠送数量</label>
                                        <input type="number" name="rules[buy_gift][gift_qty]" class="form-control" value="{{ old('rules.buy_gift.gift_qty') }}" placeholder="赠送件数">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">赠品</label>
                                        <input type="text" name="rules[buy_gift][gift_name]" class="form-control" value="{{ old('rules.buy_gift.gift_name') }}" placeholder="赠品名称">
                                    </div>
                                </div>
                            </div>
                            <!-- 秒杀规则 -->
                            <div class="rule-item" data-type="flash_sale" style="display:none;">
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label">秒杀价</label>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" step="0.01" name="rules[flash_sale][price]" class="form-control" value="{{ old('rules.flash_sale.price') }}" placeholder="秒杀价格">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">限购数量</label>
                                        <input type="number" name="rules[flash_sale][limit]" class="form-control" value="{{ old('rules.flash_sale.limit') }}" placeholder="每人限购件数">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 适用商品 -->
                    <div class="mb-3">
                        <label class="form-label">适用商品</label>
                        <select name="product_ids[]" class="form-select" multiple size="5">
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(collect(old('product_ids', []))->contains($product->id))>{{ $product->name }} ({{ $product->product_code }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">按住 Ctrl/Cmd 键可选择多个商品</div>
                    </div>
                    <!-- 状态 -->
                    <div class="mb-3">
                        <label class="form-label">状态</label>
                        <select name="status" class="form-select">
                            <option value="draft" @selected(old('status', 'draft') == 'draft')>草稿</option>
                            <option value="active" @selected(old('status') == 'active')>立即启用</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- 活动描述 -->
            <div class="mb-3">
                <label class="form-label">活动描述</label>
                <textarea name="description" class="form-control" rows="3" placeholder="请输入活动描述说明">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">创建</button>
            <a href="{{ route('promotions.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('promotionType');
    var ruleItems = document.querySelectorAll('.rule-item');

    function updateRuleVisibility() {
        var selectedType = typeSelect.value;
        ruleItems.forEach(function(item) {
            if (item.dataset.type === selectedType) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    typeSelect.addEventListener('change', updateRuleVisibility);
    updateRuleVisibility();
});
</script>
@endsection