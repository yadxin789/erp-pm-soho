@extends('soho::layouts.app')

@section('title', '店铺管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">店铺列表</h5>
        <a href="{{ route('soho.shop.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加店铺
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.shop.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索店铺名" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        <option value="taobao" @selected(request('platform') == 'taobao')>淘宝</option>
                        <option value="jd" @selected(request('platform') == 'jd')>京东</option>
                        <option value="pinduoduo" @selected(request('platform') == 'pinduoduo')>拼多多</option>
                        <option value="amazon" @selected(request('platform') == 'amazon')>亚马逊</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.shop.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店铺名称</th>
                        <th>平台</th>
                        <th>店铺编码</th>
                        <th>授权状态</th>
                        <th>Token 过期</th>
                        <th>启用</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                    <tr>
                        <td>{{ $shop->id }}</td>
                        <td>{{ $shop->name }}</td>
                        <td><span class="badge bg-info">{{ $shop->platform_label }}</span></td>
                        <td>{{ $shop->shop_code }}</td>
                        <td>
                            @if($shop->auth_status)
                                <span class="badge bg-success">已授权</span>
                            @else
                                <span class="badge bg-danger">未授权</span>
                            @endif
                        </td>
                        <td>
                            @if($shop->token_expires_at)
                                @if($shop->token_expires_at->isPast())
                                    <span class="text-danger">{{ $shop->token_expires_at->format('Y-m-d') }} (已过期)</span>
                                @else
                                    {{ $shop->token_expires_at->format('Y-m-d') }}
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $shop->status ? 'success' : 'secondary' }}">
                                {{ $shop->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.shop.edit', $shop) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i>
                            </a>

                            <!-- 检测授权 -->
                            <form method="POST" action="{{ route('soho.shop.check-auth', $shop) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info" title="检测授权">
                                    <i class="fa fa-check-circle"></i>
                                </button>
                            </form>

                            <!-- 刷新 Token -->
                            <form method="POST" action="{{ route('soho.shop.refresh-token', $shop) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="刷新 Token">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('soho.shop.destroy', $shop) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $shops->links() }}
    </div>
</div>
@endsection