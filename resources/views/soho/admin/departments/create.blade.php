@extends('soho::layouts.app')

@section('title', '添加部门')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">添加部门</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.admin.departments.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">部门名称 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">部门编码</label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">上级部门</label>
                <select name="parent_id" class="form-select">
                    <option value="">无（顶级部门）</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">排序</label>
                <input type="number" name="sort" class="form-control" value="{{ old('sort', 0) }}">
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" id="status" value="1" {{ old('status', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">启用</label>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">创建</button>
                <a href="{{ route('soho.admin.departments.index') }}" class="btn btn-secondary">返回</a>
            </div>
        </form>
    </div>
</div>
@endsection