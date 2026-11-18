@extends('soho::layouts.app')

@section('title', '用户详情 - ' . $user->name)

@section('content')
<div class="row">
    <!-- 用户基本信息 -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">用户详情</h5>
                <a href="{{ route('soho.admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i>返回列表
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                        <span class="badge bg-{{ $user->status ? 'success' : 'secondary' }}">
                            {{ $user->status ? '启用' : '禁用' }}
                        </span>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 120px;">ID</th>
                                <td>{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <th>姓名</th>
                                <td>{{ $user->real_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>手机</th>
                                <td>{{ $user->phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>部门</th>
                                <td>{{ $user->department->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>角色</th>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>最后登录</th>
                                <td>{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '从未登录' }}</td>
                            </tr>
                            <tr>
                                <th>创建时间</th>
                                <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 侧边信息 -->
    <div class="col-md-4">
        <!-- 角色权限 -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">权限概览</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">拥有角色</p>
                <ul class="list-group list-group-flush">
                    @forelse($user->roles as $role)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $role->name }}
                            <span class="badge bg-primary">{{ $role->permissions->count() }} 权限</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">暂无角色</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 限制店铺 -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">店铺权限</h6>
            </div>
            <div class="card-body">
                @php
                    $limitedShops = $user->limitedShops;
                @endphp
                
                @if($limitedShops && $limitedShops->isNotEmpty())
                    <p class="text-muted mb-2">可管理店铺</p>
                    <ul class="list-group list-group-flush">
                        @foreach($limitedShops as $shop)
                            <li class="list-group-item">
                                <i class="fa fa-shopping-cart me-2"></i>{{ $shop->name }}
                                <span class="badge bg-info">{{ $shop->platform }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">默认可管理所有店铺</p>
                @endif
            </div>
        </div>

        <!-- 操作按钮 -->
        <div class="d-grid gap-2">
            <a href="{{ route('soho.admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fa fa-edit me-1"></i>编辑用户
            </a>
            <form method="POST" action="{{ route('soho.admin.users.destroy', $user) }}" onsubmit="return confirm('确定删除此用户？')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger w-100">
                    <i class="fa fa-trash me-1"></i>删除用户
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
