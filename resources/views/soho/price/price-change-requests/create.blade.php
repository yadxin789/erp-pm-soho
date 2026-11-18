@extends('soho::layouts.app')

@section('title', '新建调价申请')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('price.price-change-requests.index') }}">调价申请</a></li>
        <li class="breadcrumb-item active">新建申请</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-plus-circle me-2"></i>新建调价申请</h4>
    <a href="{{ route('price.price-change-requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i>返回列表
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('price.price-change-requests.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">选择商品 <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select" id="productSelect" required>
                            <option value="">请选择商品</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->current_price ?? 0 }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->product_code }}) - ¥{{ number_format($product->current_price ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">选择商品后将自动获取当前价格</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="选填，具体SKU编码">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">当前价格 (¥)</label>
                            <input type="number" step="0.01" name="current_price" id="currentPrice" class="form-control" value="{{ old('current_price') }}" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">申请价格 (¥) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="requested_price" id="requestedPrice" class="form-control" value="{{ old('requested_price') }}" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">价格变动</label>
                            <div id="changeIndicator" class="form-control bg-light">
                                <span class="text-muted">选择商品后自动计算</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">调价原因 <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            <option value="">请选择原因</option>
                            @foreach($reasonOptions ?? ['成本调整', '市场竞争', '促销活动', '清库存', '策略调整', '其他'] as $reason)
                                <option value="{{ $reason }}" {{ old('reason') == $reason ? 'selected' : '' }}>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="详细说明调价原因和预期效果">{{ old('remark') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('price.price-change-requests.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane me-1"></i>提交申请
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('productSelect')?.addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    var price = selected.getAttribute('data-price') || 0;
    document.getElementById('currentPrice').value = price;
    updateChangeIndicator();
});

document.getElementById('requestedPrice')?.addEventListener('input', updateChangeIndicator);

function updateChangeIndicator() {
    var current = parseFloat(document.getElementById('currentPrice').value) || 0;
    var requested = parseFloat(document.getElementById('requestedPrice').value) || 0;
    var indicator = document.getElementById('changeIndicator');

    if (current > 0 && requested > 0) {
        var change = ((requested - current) / current) * 100;
        var colorClass = change > 0 ? 'text-success' : (change < 0 ? 'text-danger' : 'text-muted');
        var icon = change > 0 ? 'fa-arrow-up' : (change < 0 ? 'fa-arrow-down' : 'fa-minus');
        indicator.innerHTML = '<i class="fa ' + icon + ' ' + colorClass + '"></i> <span class="' + colorClass + ' fw-semibold">' + (change >= 0 ? '+' : '') + change.toFixed(2) + '%</span>';
    } else {
        indicator.innerHTML = '<span class="text-muted">选择商品后自动计算</span>';
    }
}
</script>
@endsection