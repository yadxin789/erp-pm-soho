@extends('soho::layouts.app')

@section('title', '执行中任务列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">执行中任务</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-play-circle me-2"></i>执行中拣货任务</h4>
    <div>
        <a href="{{ route('soho.pick.tasks.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-clock me-1"></i>待拣货任务
        </a>
        <a href="{{ route('soho.pick.tasks.completed') }}" class="btn btn-outline-success btn-sm">
            <i class="fa fa-check-circle me-1"></i>已完成任务
        </a>
    </div>
</div>

{{-- 执行中任务卡片 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">执行中任务</h5>
        <span class="badge bg-warning text-dark">{{ $progressTasks->total() }} 个任务进行中</span>
    </div>
    <div class="card-body">
        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>任务编号</th>
                        <th>订单号</th>
                        <th>仓库</th>
                        <th>拣货员</th>
                        <th>进度</th>
                        <th>已用时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($progressTasks as $task)
                    <tr>
                        <td><code>{{ $task->task_no }}</code></td>
                        <td>{{ $task->order_no ?? '-' }}</td>
                        <td>{{ $task->warehouse->name ?? '-' }}</td>
                        <td>{{ $task->picker->name ?? '-' }}</td>
                        <td style="min-width: 200px;">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $task->progress_percent ?? 0 }}%;">
                                    {{ $task->picked_count ?? 0 }} / {{ $task->items_count ?? 0 }}
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $elapsed = $task->started_at ? $task->started_at->diffForHumans(now(), true) : '-';
                            @endphp
                            {{ $elapsed }}
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">拣货中</span>
                        </td>
                        <td>
                            <a href="{{ route('soho.pick.tasks.scan', $task) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-barcode"></i> 继续拣货
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="pauseTask({{ $task->id }})">
                                <i class="fa fa-pause"></i> 暂停
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无执行中的拣货任务
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $progressTasks->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function pauseTask(id) {
    if (confirm('确认暂停该拣货任务？')) {
        // 暂停任务逻辑
    }
}
</script>
@endsection