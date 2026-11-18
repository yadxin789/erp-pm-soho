@extends('soho::layouts.app')

@section('title', '异常中心')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">异常中心</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">异常列表</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.exceptions.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索异常标题/编号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="order" @selected(request('type') == 'order')>订单异常</option>
                        <option value="sync" @selected(request('type') == 'sync')>同步异常</option>
                        <option value="api" @selected(request('type') == 'api')>API异常</option>
                        <option value="stock" @selected(request('type') == 'stock')>库存异常</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="open" @selected(request('status') == 'open')>待处理</option>
                        <option value="processing" @selected(request('status') == 'processing')>处理中</option>
                        <option value="resolved" @selected(request('status') == 'resolved')>已解决</option>
                        <option value="ignored" @selected(request('status') == 'ignored')>已忽略</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.exceptions.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>异常标题</th>
                        <th>类型</th>
                        <th>严重程度</th>
                        <th>关联单号</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions as $exception)
                    <tr>
                        <td>{{ $exception->id }}</td>
                        <td>{{ $exception->title }}</td>
                        <td><span class="badge bg-info">{{ $exception->type_label ?? $exception->type }}</span></td>
                        <td>
                            @if($exception->severity == 'high')
                                <span class="badge bg-danger">高</span>
                            @elseif($exception->severity == 'medium')
                                <span class="badge bg-warning text-dark">中</span>
                            @else
                                <span class="badge bg-secondary">低</span>
                            @endif
                        </td>
                        <td>{{ $exception->related_no ?? '-' }}</td>
                        <td>
                            @if($exception->status == 'open')
                                <span class="badge bg-danger">待处理</span>
                            @elseif($exception->status == 'processing')
                                <span class="badge bg-warning text-dark">处理中</span>
                            @elseif($exception->status == 'resolved')
                                <span class="badge bg-success">已解决</span>
                            @else
                                <span class="badge bg-secondary">已忽略</span>
                            @endif
                        </td>
                        <td>{{ $exception->created_at ? $exception->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('soho.exceptions.show', $exception) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exceptions->links() }}
    </div>
</div>
@endsection