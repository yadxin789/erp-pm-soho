@extends('soho::layouts.app')

@section('title', '编辑配送方式')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.shipping-method.index') }}">配送方式管理</a></li>
        <li class="breadcrumb-item active">编辑配送方式</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">编辑配送方式：{{ $shippingMethod->name }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.shipping-method.update', $shippingMethod) }}">
            @csrf @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $shippingMethod->name) }}" required maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">编码 <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $shippingMethod->code) }}" required maxlength="50">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">计费类型 <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="fixed" @selected(old('type', $shippingMethod->type) == 'fixed')>固定运费</option>
                        <option value="weight" @selected(old('type', $shippingMethod->type) == 'weight')>按重量计费</option>
                        <option value="quantity" @selected(old('type', $shippingMethod->type) == 'quantity')>按件数计费</option>
                        <option value="region" @selected(old('type', $shippingMethod->type) == 'region')>按地区计费</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">状态 <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $shippingMethod->status) == 1)>启用</option>
                        <option value="0" @selected(old('status', $shippingMethod->status) == 0)>禁用</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort" class="form-control" value="{{ old('sort', $shippingMethod->sort) }}" min="0">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('soho.shipping-method.index') }}" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> 保存</button>
            </div>
        </form>
    </div>
</div>
@endsection
