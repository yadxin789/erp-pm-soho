@extends('soho::layouts.app')

@section('title', '待复核任务')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">待复核任务</li>
    </ol>
</nav>

{{-- 页面标题栏 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-clipboard-check me-2"></i>待复核任务列表</h4>
    <div>
        <a href="{{ route('soho.check.tasks.completed') }}" class="btn btn-outline-success btn-sm me-2">
            <i class="fa fa-check-circle me-1"></i>已完成复核
        </a>
        <a href="{{ route('soho.check.tasks.record') }}" class="btn btn-outline-info btn-sm me-2">
            <i class="fa fa-history me-1"></i>复核记录
        </a>
        <a href="{{ route('soho.check.tasks.exception') }}" class="btn btn-outline-warning btn-sm">
            <i class="fa fa-exclamation-triangle me-1"></i>复核异常
        </a>
    </div>
</div>

{{-- 复核任务列表卡片 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待复核任务</h5>
        <div>
            <span class="badge bg-info me-2">共 {{ $checkTasks->total() }} 条</span>
            <button type="button" class="btn btn-success btn-sm">
                <i class="fa fa-clipboard-check me-1"></i>批量复核
            </button>
        </div>
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
                    <select name="source" class="form-select">
                        <option value="">全部来源</option>
                        <option value="pick" @selected(request('source') == 'pick')>拣货完成</option>
                        <option value="return" @selected(request('source') == 'return')>退货入库</option>
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
                    <a href="{{ route('soho.check.tasks.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>复核单号</th>
                        <th>订单号</th>
                        <th>来源</th>
                        <th>仓库</th>
                        <th>商品数</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checkTasks as $task)
                    <tr>
                        <td><input type="checkbox" name="task_ids[]" value="{{ $task->id }}"></td>
                        <td><code>{{ $task->check_no }}</code></td>
                        <td>{{ $task->order_no ?? '-' }}</td>
                        <td>
                            @if($task->source == 'pick')
                                <span class="badge bg-info">拣货完成</span>
                            @elseif($task->source == 'return')
                                <span class="badge bg-warning text-dark">退货入库</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>{{ $task->warehouse->name ?? '-' }}</td>
                        <td>{{ $task->items_count ?? 0 }} 件</td>
                        <td>
                            <span class="badge bg-info">待复核</span>
                        </td>
                        <td>{{ optional($task->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('soho.check.tasks.scan', $task) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-barcode"></i> 扫码复核
                            </a>
                            <a href="{{ route('soho.check.tasks.index', $task) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无待复核任务
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $checkTasks->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="task_ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection