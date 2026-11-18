@extends('soho::layouts.app')

@section('title', '编辑平台 SKU 映射')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑平台 SKU 映射</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.platform-sku.update', $platformSku) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">店铺 <span class="text-danger">*</span></label>
                        <select name="shop_id" class="form-select" required>
                            <option value="">请选择店铺</option>
                            @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" @selected(old('shop_id', $platformSku->shop_id) == $shop->id)>{{ $shop->shop_name }} ({{ $shop->platform_label }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">系统商品 <span class="text-danger">*</span></label>
                        <select name="system_goods_id" class="form-select" required>
                            <option value="">请选择系统商品</option>
                            @foreach($systemGoods as $goods)
                            <option value="{{ $goods->id }}" @selected(old('system_goods_id', $platformSku->system_goods_id) == $goods->id)>{{ $goods->goods_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">平台 SKU <span class="text-danger">*</span></label>
                        <input type="text" name="platform_sku" class="form-control" value="{{ old('platform_sku', $platformSku->platform_sku) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">平台商品ID</label>
                        <input type="text" name="platform_goods_id" class="form-control" value="{{ old('platform_goods_id', $platformSku->platform_goods_id) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">平台商品链接</label>
                        <input type="url" name="platform_goods_url" class="form-control" value="{{ old('platform_goods_url', $platformSku->platform_goods_url) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">平台价格</label>
                        <input type="number" step="0.01" name="platform_price" class="form-control" value="{{ old('platform_price', $platformSku->platform_price) }}" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">平台库存</label>
                        <input type="number" name="platform_stock" class="form-control" value="{{ old('platform_stock', $platformSku->platform_stock) }}" min="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="stock_sync_enabled" class="form-check-input" value="1" @checked(old('stock_sync_enabled', $platformSku->stock_sync_enabled))>
                            <label class="form-check-label">启用库存同步</label>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="{{ route('soho.platform-sku.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection