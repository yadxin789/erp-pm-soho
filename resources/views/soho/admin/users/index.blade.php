@extends('soho::layouts.app')

@section('title', '用户管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">用户列表</h5>
        <a href="{{ route('soho.admin.users.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加用户
        </a>
    </div>
    <div class="card-body">
        <!-- 搜索表单 -->
        <form method="GET" action="{{ route('soho.admin.users.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索用户名或邮箱" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">搜索</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>真实姓名</th>
                        <th>部门</th>
                        <th>角色</th>
                        <th>状态</th>
                        <th>最后登录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->real_name ?? '-' }}</td>
                        <td>{{ $user->department->name ?? '-' }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->status ? 'success' : 'secondary' }}">
                                {{ $user->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>{{ $user->last_login_at ?? '-' }}</td>
                        <td>
                            <a href="{{ route('soho.admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">编辑</a>
                            <form method="POST" action="{{ route('soho.admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">暂无数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</div>
@endsection