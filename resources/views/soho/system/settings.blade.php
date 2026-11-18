@extends('soho::layouts.app')

@section('title', '系统设置')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">系统设置</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.system.settings.update') }}">
            @csrf
            @method('PUT')

            <!-- 基础设置 -->
            <h6 class="mb-3 text-primary"><i class="fa fa-cog me-2"></i>基础设置</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="shop_name" class="form-label">店铺名称</label>
                        <input type="text" class="form-control @error('shop_name') is-invalid @enderror" 
                               id="shop_name" name="shop_name" 
                               value="{{ old('shop_name', $configs['shop_name'] ?? '') }}">
                        @error('shop_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">联系电话</label>
                        <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                               id="contact_phone" name="contact_phone" 
                               value="{{ old('contact_phone', $configs['contact_phone'] ?? '') }}">
                        @error('contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 库存设置 -->
            <h6 class="mb-3 text-primary"><i class="fa fa-warehouse me-2"></i>库存设置</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="low_stock_threshold" class="form-label">库存预警阈值</label>
                        <input type="number" class="form-control @error('low_stock_threshold') is-invalid @enderror" 
                               id="low_stock_threshold" name="low_stock_threshold" min="0"
                               value="{{ old('low_stock_threshold', $configs['low_stock_threshold'] ?? 10) }}">
                        <div class="form-text">当库存数量低于此值时触发预警</div>
                        @error('low_stock_threshold')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 自动同步设置 -->
            <h6 class="mb-3 text-primary"><i class="fa fa-refresh me-2"></i>自动同步设置</h6>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_sync_orders" 
                               name="auto_sync_orders" value="1"
                               {{ old('auto_sync_orders', $configs['auto_sync_orders'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto_sync_orders">自动同步订单</label>
                    </div>
                    <div class="mb-3">
                        <label for="order_sync_interval" class="form-label">订单同步间隔（分钟）</label>
                        <input type="number" class="form-control @error('order_sync_interval') is-invalid @enderror" 
                               id="order_sync_interval" name="order_sync_interval" min="1"
                               value="{{ old('order_sync_interval', $configs['order_sync_interval'] ?? 30) }}">
                        @error('order_sync_interval')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_sync_stock" 
                               name="auto_sync_stock" value="1"
                               {{ old('auto_sync_stock', $configs['auto_sync_stock'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto_sync_stock">自动同步库存</label>
                    </div>
                    <div class="mb-3">
                        <label for="stock_sync_interval" class="form-label">库存同步间隔（分钟）</label>
                        <input type="number" class="form-control @error('stock_sync_interval') is-invalid @enderror" 
                               id="stock_sync_interval" name="stock_sync_interval" min="1"
                               value="{{ old('stock_sync_interval', $configs['stock_sync_interval'] ?? 60) }}">
                        @error('stock_sync_interval')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i>保存设置
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
