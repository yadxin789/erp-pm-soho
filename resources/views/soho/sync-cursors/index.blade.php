@extends('soho::layouts.app')

@section('title', '同步游标')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">同步游标</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">同步游标列表</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.sync_cursors.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索实体类型/ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="entity_type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="order" @selected(request('entity_type') == 'order')>订单</option>
                        <option value="product" @selected(request('entity_type') == 'product')>商品</option>
                        <option value="inventory" @selected(request('entity_type') == 'inventory')>库存</option>
                        <option value="customer" @selected(request('entity_type') == 'customer')>客户</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.sync_cursors.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>实体类型</th>
                        <th>实体ID</th>
                        <th>平台</th>
                        <th>最后同步游标</th>
                        <th>最后同步时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($syncCursors as $cursor)
                    <tr>
                        <td>{{ $cursor->id }}</td>
                        <td><span class="badge bg-info">{{ $cursor->entity_type }}</span></td>
                        <td>{{ $cursor->entity_id }}</td>
                        <td>{{ $cursor->platform ?? '-' }}</td>
                        <td><code>{{ $cursor->last_cursor ?? '-' }}</code></td>
                        <td>{{ $cursor->last_sync_at ? $cursor->last_sync_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td>
                            @if($cursor->status == 'active')
                                <span class="badge bg-success">正常</span>
                            @else
                                <span class="badge bg-danger">异常</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('soho.sync_cursors.reset', $cursor) }}" class="d-inline" onsubmit="return confirm('确认重置此游标？重置后将从头开始同步。')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="fa fa-undo"></i> 重置
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
        {{ $syncCursors->links() }}
    </div>
</div>
@endsection