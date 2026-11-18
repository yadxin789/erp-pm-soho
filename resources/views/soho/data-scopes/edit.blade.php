@extends('soho::layouts.app')

@section('title', '编辑数据权限')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.data_scopes.index') }}">数据权限</a></li>
        <li class="breadcrumb-item active">编辑权限</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑数据权限</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.data_scopes.update', $dataScope) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">权限名称 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $dataScope->name) }}" required>
                @if($errors->first('name'))
                    <div class="text-danger small mt-1">{{ $errors->first('name') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">角色 <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                    <option value="">选择角色</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected($dataScope->role_id == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @if($errors->first('role_id'))
                    <div class="text-danger small mt-1">{{ $errors->first('role_id') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">数据范围 <span class="text-danger">*</span></label>
                <select name="scope_type" class="form-select" required>
                    <option value="all" @selected($dataScope->scope_type == 'all')>全部数据</option>
                    <option value="department" @selected($dataScope->scope_type == 'department')>本部门</option>
                    <option value="department_and_children" @selected($dataScope->scope_type == 'department_and_children')>本部门及以下</option>
                    <option value="self" @selected($dataScope->scope_type == 'self')>仅本人</option>
                    <option value="custom" @selected($dataScope->scope_type == 'custom')>自定义</option>
                </select>
                @if($errors->first('scope_type'))
                    <div class="text-danger small mt-1">{{ $errors->first('scope_type') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">模块</label>
                <input type="text" name="module" class="form-control" value="{{ old('module', $dataScope->module) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">自定义条件</label>
                <textarea name="conditions" class="form-control" rows="3">{{ old('conditions', $dataScope->conditions) }}</textarea>
                <small class="text-muted">当数据范围为"自定义"时使用</small>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" value="1" @checked(old('status', $dataScope->status))>
                    <label class="form-check-label">启用</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 保存
            </button>
            <a href="{{ route('soho.data_scopes.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection