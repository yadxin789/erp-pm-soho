@extends('soho::layouts.app')

@section('title', '待打包任务')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">待打包任务</li>
    </ol>
</nav>

{{-- 页面标题栏 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-box me-2"></i>待打包任务列表</h4>
    <div>
        <a href="{{ route('fulfillment.pack-tasks.completed') }}" class="btn btn-outline-success btn-sm me-2">
            <i class="fa fa-check-circle me-1"></i>已完成打包
        </a>
        <a href="{{ route('fulfillment.pack-tasks.exception') }}" class="btn btn-outline-warning btn-sm">
            <i class="fa fa-exclamation-triangle me-1"></i>打包异常
        </a>
    </div>
</div>

{{-- 打包任务列表卡片 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待打包任务</h5>
        <div>
            <span class="badge bg-info me-2">共 {{ $packTasks->total() }} 条</span>
        </div>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="打包单号/订单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="pack_type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="normal" @selected(request('pack_type') == 'normal')>普通打包</option>
                        <option value="gift" @selected(request('pack_type') == 'gift')>礼品包装</option>
                        <option value="fragile" @selected(request('pack_type') == 'fragile')>易碎品</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('fulfillment.pack-tasks.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>打包单号</th>
                        <th>订单号</th>
                        <th>仓库</th>
                        <th>商品数</th>
                        <th>打包类型</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packTasks as $task)
                    <tr>
                        <td><code>{{ $task->pack_no }}</code></td>
                        <td>{{ $task->order_no ?? '-' }}</td>
                        <td>{{ $task->warehouse->name ?? '-' }}</td>
                        <td>{{ $task->items_count ?? 0 }} 件</td>
                        <td>
                            @if($task->pack_type == 'normal')
                                <span class="badge bg-info">普通</span>
                            @elseif($task->pack_type == 'gift')
                                <span class="badge bg-purple">{{ $task->pack_type }}</span>
                            @elseif($task->pack_type == 'fragile')
                                <span class="badge bg-warning text-dark">易碎</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">待打包</span>
                        </td>
                        <td>{{ optional($task->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('fulfillment.pack-tasks.scan', $task) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-barcode"></i> 扫码打包
                            </a>
                            <a href="{{ route('fulfillment.pack-tasks.show', $task) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无待打包任务
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $packTasks->links() }}
        </div>
    </div>
</div>
@endsection