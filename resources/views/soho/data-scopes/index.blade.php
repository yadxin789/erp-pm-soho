@extends('soho::layouts.app')

@section('title', '数据权限')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">数据权限</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">数据权限列表</h5>
        <a href="{{ route('soho.data_scopes.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 创建权限
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.data_scopes.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索权限名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="scope_type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="all" @selected(request('scope_type') == 'all')>全部数据</option>
                        <option value="department" @selected(request('scope_type') == 'department')>本部门</option>
                        <option value="department_and_children" @selected(request('scope_type') == 'department_and_children')>本部门及以下</option>
                        <option value="self" @selected(request('scope_type') == 'self')>仅本人</option>
                        <option value="custom" @selected(request('scope_type') == 'custom')>自定义</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.data_scopes.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>权限名称</th>
                        <th>角色</th>
                        <th>数据范围</th>
                        <th>模块</th>
                        <th>状态</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataScopes as $scope)
                    <tr>
                        <td>{{ $scope->id }}</td>
                        <td>{{ $scope->name }}</td>
                        <td>{{ $scope->role->name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $scope->scope_type_label ?? $scope->scope_type }}</span></td>
                        <td>{{ $scope->module ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $scope->status ? 'success' : 'secondary' }}">
                                {{ $scope->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>{{ $scope->updated_at ? $scope->updated_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('soho.data_scopes.edit', $scope) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $dataScopes->links() }}
    </div>
</div>
@endsection