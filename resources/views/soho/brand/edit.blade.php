@extends('soho::layouts.app')
@section('title', '编辑品牌')
@section('content')
<div class="card"><div class="card-header"><h5 class="mb-0">编辑品牌</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.brand.update', $brand) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $brand->name) }}" required></div>
            <div class="mb-3"><label class="form-label">编码</label><input type="text" name="code" class="form-control" value="{{ old('code', $brand->code) }}"></div>
            <div class="mb-3"><label class="form-label">描述</label><textarea name="description" class="form-control">{{ old('description', $brand->description) }}</textarea></div>
            <div class="mb-3"><label class="form-label">排序</label><input type="number" name="sort" class="form-control" value="{{ old('sort', $brand->sort) }}"></div>
            <div class="mb-3"><div class="form-check"><input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $brand->status) ? 'checked' : '' }}><label class="form-check-label">启用</label></div></div>
            <button type="submit" class="btn btn-primary">保存</button><a href="{{ route('soho.brand.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection