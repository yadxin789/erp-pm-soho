@extends('soho::layouts.app')

@section('title', '编辑商品')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑商品</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.goods.update', $goods) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6"><div class="mb-3"><label class="form-label">商品名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $goods->name) }}" required></div></div>
                <div class="col-md-6"><div class="mb-3"><label class="form-label">SKU <span class="text-danger">*</span></label><input type="text" name="sku" class="form-control" value="{{ old('sku', $goods->sku) }}" required></div></div>
            </div>
            <div class="mb-3"><label class="form-label">描述</label><textarea name="description" class="form-control" rows="3">{{ old('description', $goods->description) }}</textarea></div>
            <div class="mb-3"><div class="form-check"><input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $goods->status) ? 'checked' : '' }}><label class="form-check-label">启用</label></div></div>
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="{{ route('soho.goods.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection