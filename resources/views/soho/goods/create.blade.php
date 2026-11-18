@extends('soho::layouts.app')

@section('title', '添加商品')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">添加商品</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.goods.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">商品名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">SKU <span class="text-danger">*</span></label><input type="text" name="sku" class="form-control" value="{{ old('sku') }}" required></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">条形码</label><input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}"></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">品牌</label><input type="text" name="brand_id" class="form-control" value="{{ old('brand_id') }}"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">成本价</label><input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price') }}"></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">零售价</label><input type="number" step="0.01" name="retail_price" class="form-control" value="{{ old('retail_price') }}"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">重量(kg)</label><input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight') }}"></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3"><label class="form-label">分类</label><input type="text" name="category_id" class="form-control" value="{{ old('category_id') }}"></div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">描述</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <div class="form-check"><input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', true) ? 'checked' : '' }}><label class="form-check-label">启用</label></div>
            </div>
            <button type="submit" class="btn btn-primary">创建</button>
            <a href="{{ route('soho.goods.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection