@extends('soho::layouts.app')

@section('title', '调价任务')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">价格中心</a></li>
        <li class="breadcrumb-item active">调价任务</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-tasks me-2"></i>调价任务</h4>
    <a href="{{ route('price.price-change-tasks.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新建任务
    </a>
</div>

{{-- 筛选表单 --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('price.price-change-tasks.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">任务类型</label>
                <select class="form-select" name="type">
                    <option value="">全部类型</option>
                    @foreach($typeLabels ?? [] as $value => $label)
                        <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">状态</label>
                <select class="form-select" name="status">
                    <option value="">全部状态</option>
                    @foreach($statusLabels ?? [] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">执行人</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="执行人姓名">
            </div>
            <div class="col-md-2">
                <label class="form-label">计划时间</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-1">
                    <i class="fa fa-search"></i> 筛选
                </button>
                <a href="{{ route('price.price-change-tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 任务列表 --}}
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-list me-2"></i>任务列表</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>任务类型</th>
                        <th>目标</th>
                        <th>目标价格</th>
                        <th>状态</th>
                        <th>计划时间</th>
                        <th>执行人</th>
                        <th width="200">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $index => $task)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-info">{{ $typeLabels[$task->type] ?? $task->type }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $task->target->name ?? '-' }}</div>
                                <code class="small text-muted">{{ $task->target_id }}</code>
                            </td>
                            <td class="fw-semibold text-primary">
                                ¥{{ number_format($task->target_price ?? 0, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'running' => 'primary',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$task->status] ?? $task->status }}
                                </span>
                            </td>
                            <td class="small">
                                {{ $task->planned_at?->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td class="small">{{ $task->executor->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('price.price-change-tasks.show', $task) }}" class="btn btn-sm btn-outline-primary" title="查看详情">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @if($task->status === 'pending')
                                    <form method="POST" action="{{ route('price.price-change-tasks.execute', $task) }}" class="d-inline" onsubmit="return confirm('确定立即执行此任务？')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="立即执行">
                                            <i class="fa fa-play"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('price.price-change-tasks.cancel', $task) }}" class="d-inline" onsubmit="return confirm('确定取消此任务？')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="取消">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($tasks, 'links'))
        <div class="card-footer">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection