@extends('soho::layouts.app')
@section('title', '编辑分类')
@section('content')
<div class="card"><div class="card-header"><h5 class="mb-0">编辑分类</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.category.update', $category) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required></div>
            <div class="mb-3"><label class="form-label">上级分类</label><select name="parent_id" class="form-select"><option value="">无</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('parent_id', $category->parent_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">别名</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}"></div>
              <div class="mb-3"><label class="form-label">排序</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}"></div>
            <div class="mb-3"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}><label class="form-check-label">启用</label></div></div>
            <button type="submit" class="btn btn-primary">保存</button><a href="{{ route('soho.category.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection