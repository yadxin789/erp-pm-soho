@extends('soho::layouts.app')

@section('title', '角色管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">角色列表</h5>
        <a href="{{ route('soho.admin.roles.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加角色
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>角色名称</th>
                        <th>权限数量</th>
                        <th>用户数量</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->permissions->count() }}</td>
                        <td>{{ $role->users->count() }}</td>
                        <td>{{ $role->created_at }}</td>
                        <td>
                            <a href="{{ route('soho.admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">编辑</a>
                            <form method="POST" action="{{ route('soho.admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">暂无数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $roles->links() }}
    </div>
</div>
@endsection