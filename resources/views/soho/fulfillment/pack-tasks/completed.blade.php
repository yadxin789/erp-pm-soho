@extends('soho::layouts.app')

@section('title', '已完成打包列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">已完成打包</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-check-circle me-2"></i>已完成打包列表</h4>
    <div>
        <a href="{{ route('soho.pack.tasks.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-clock me-1"></i>待打包任务
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
                <div class="text-muted small">今日打包</div>
                <div class="fs-3 text-success">{{ $todayStats['count'] ?? 0 }}</div>
                <div class="text-muted small">任务数</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">今日包裹量</div>
                <div class="fs-3 text-info">{{ $todayStats['parcels'] ?? 0 }}</div>
                <div class="text-muted small">包裹数</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">总重量</div>
                <div class="fs-3 text-warning">{{ $todayStats['total_weight'] ?? 0 }}</div>
                <div class="text-muted small">kg</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">异常率</div>
                <div class="fs-3 text-danger">{{ $todayStats['error_rate'] ?? '0%' }}</div>
                <div class="text-muted small">异常占比</div>
            </div>
        </div>
    </div>
</div>

{{-- 已完成打包列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">已完成打包</h5>
        <span class="badge bg-success">{{ $completedTasks->total() }} 条记录</span>
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
                    <select name="packer_id" class="form-select">
                        <option value="">全部打包员</option>
                        @foreach($packers as $packer)
                            <option value="{{ $packer->id }}" @selected(request('packer_id') == $packer->id)>{{ $packer->name }}</option>
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
                    <a href="{{ route('soho.pack.tasks.completed') }}" class="btn btn-outline-secondary">重置</a>
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
                        <th>打包员</th>
                        <th>包裹数</th>
                        <th>总重量</th>
                        <th>状态</th>
                        <th>完成时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedTasks as $task)
                    <tr>
                        <td><code>{{ $task->pack_no }}</code></td>
                        <td>{{ $task->order_no ?? '-' }}</td>
                        <td>{{ $task->warehouse->name ?? '-' }}</td>
                        <td>{{ $task->packer->name ?? '-' }}</td>
                        <td>{{ $task->parcel_count ?? 0 }} 个</td>
                        <td>{{ $task->total_weight ?? 0 }} kg</td>
                        <td>
                            <span class="badge bg-success">已完成</span>
                            @if($task->has_exception)
                                <span class="badge bg-warning text-dark">有异常</span>
                            @endif
                        </td>
                        <td>{{ optional($task->completed_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('soho.pack.tasks.index', $task) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无已完成的打包任务
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
    if (confirm('确认导出打包完成数据？')) {
        // 导出逻辑
    }
}
</script>
@endsection