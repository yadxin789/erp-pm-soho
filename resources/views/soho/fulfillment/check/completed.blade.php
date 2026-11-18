@extends('soho::layouts.app')
@php $checkers = $checkers ?? collect(); @endphp


@section('title', '已完成复核列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">已完成复核</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-check-circle me-2"></i>已完成复核列表</h4>
    <div>
        <a href="{{ route('soho.check.tasks.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-clock me-1"></i>待复核任务
        </a>
        <button type="button" class="btn btn-success btn-sm" onclick="exportData()">
            <i class="fa fa-download me-1"></i>导出数据
        </button>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">今日复核</div>
                <div class="fs-3 text-success">{{ $todayStats['count'] ?? 0 }}</div>
                <div class="text-muted small">任务数</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">今日复核量</div>
                <div class="fs-3 text-info">{{ $todayStats['items'] ?? 0 }}</div>
                <div class="text-muted small">商品件数</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">差异单数</div>
                <div class="fs-3 text-warning">{{ $todayStats['diff_count'] ?? 0 }}</div>
                <div class="text-muted small">存在差异</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">复核准确率</div>
                <div class="fs-3 text-success">{{ $todayStats['accuracy'] ?? '100%' }}</div>
                <div class="text-muted small">正确率</div>
            </div>
        </div>
    </div>
</div>

{{-- 已完成复核列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">已完成复核</h5>
        <span class="badge bg-success">{{ $completedTasks->total() }} 条记录</span>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="复核单号/订单号" value="{{ request('search') }}">
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
                    <select name="checker_id" class="form-select">
                        <option value="">全部复核员</option>
                        @foreach($checkers as $checker)
                            <option value="{{ $checker->id }}" @selected(request('checker_id') == $checker->id)>{{ $checker->name }}</option>
                        @endforeach
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
                    <a href="{{ route('soho.check.tasks.completed') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>复核单号</th>
                        <th>订单号</th>
                        <th>仓库</th>
                        <th>复核员</th>
                        <th>商品数</th>
                        <th>差异</th>
                        <th>状态</th>
                        <th>完成时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedTasks as $task)
                    <tr>
                        <td><code>{{ $task->check_no }}</code></td>
                        <td>{{ $task->order_no ?? '-' }}</td>
                        <td>{{ $task->warehouse->name ?? '-' }}</td>
                        <td>{{ $task->checker->name ?? '-' }}</td>
                        <td>{{ $task->items_count ?? 0 }} 件</td>
                        <td>
                            @if(($task->diff_count ?? 0) > 0)
                                <span class="badge bg-warning text-dark">{{ $task->diff_count }} 处差异</span>
                            @else
                                <span class="badge bg-success">无差异</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">已完成</span>
                            @if($task->has_exception)
                                <span class="badge bg-danger">有异常</span>
                            @endif
                        </td>
                        <td>{{ optional($task->completed_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('soho.check.tasks.index', $task) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无已完成的复核任务
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $completedTasks->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportData() {
    if (confirm('确认导出复核完成数据？')) {
        // 导出逻辑
    }
}
</script>
@endsection