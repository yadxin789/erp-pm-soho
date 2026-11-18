@extends('soho::layouts.app')

@section('title', '调价任务详情')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('price.price-change-tasks.index') }}">调价任务</a></li>
        <li class="breadcrumb-item active">任务详情</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-tasks me-2"></i>任务详情</h4>
            <div>
                @if($task->status === 'pending')
                    <form method="POST" action="{{ route('price.price-change-tasks.execute', $task) }}" class="d-inline" onsubmit="return confirm('确定立即执行此任务？')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-play me-1"></i>立即执行
                        </button>
                    </form>
                    <form method="POST" action="{{ route('price.price-change-tasks.cancel', $task) }}" class="d-inline" onsubmit="return confirm('确定取消此任务？')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-times me-1"></i>取消任务
                        </button>
                    </form>
                @endif
                <a href="{{ route('price.price-change-tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i>返回列表
                </a>
            </div>
        </div>

        {{-- 任务状态卡片 --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'running' => 'primary',
                            'completed' => 'success',
                            'failed' => 'danger',
                            'cancelled' => 'secondary',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }} fs-6">
                        {{ $statusLabels[$task->status] ?? $task->status }}
                    </span>
                    <span class="badge bg-info ms-2">{{ $typeLabels[$task->type] ?? $task->type }}</span>
                </div>
                <span class="small text-muted">任务编号: #{{ $task->id }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="140">关联申请</th><td>
                                @if($task->request_id)
                                    <a href="{{ route('price.price-change-requests.show', $task->request) }}">#{{ $task->request_id }}</a>
                                @else
                                    <span class="text-muted">无</span>
                                @endif
                            </td></tr>
                            <tr><th>目标类型</th><td>{{ $task->target_type ?? '-' }}</td></tr>
                            <tr><th>目标 ID</th><td>{{ $task->target_id ?? '-' }}</td></tr>
                            <tr><th>目标对象</th><td>{{ $task->target->name ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="140">目标价格</th><td class="fw-bold text-primary" style="font-size: 1.2rem;">¥{{ number_format($task->target_price ?? 0, 2) }}</td></tr>
                            <tr><th>计划执行时间</th><td>{{ $task->planned_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
                            <tr><th>执行人</th><td>{{ $task->executor->name ?? '-' }}</td></tr>
                            <tr><th>完成时间</th><td>{{ $task->completed_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <table class="table table-borderless mb-0">
                    <tr><th width="140">备注</th><td>{{ $task->remark ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- 执行结果 --}}
        @if($task->status === 'completed')
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-check-circle me-2"></i>执行结果</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="text-muted small">执行平台数</div>
                        <div class="display-6 text-primary">{{ $task->platform_count ?? 0 }}</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="text-muted small">成功数量</div>
                        <div class="display-6 text-success">{{ $task->success_count ?? 0 }}</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="text-muted small">失败数量</div>
                        <div class="display-6 text-danger">{{ $task->failed_count ?? 0 }}</div>
                    </div>
                </div>
                @if($task->result_message)
                <hr>
                <div class="alert alert-light border mb-0">
                    <h6 class="small text-muted mb-1"><i class="fa fa-comment me-1"></i>执行日志</h6>
                    <pre class="mb-0 small">{{ $task->result_message }}</pre>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($task->status === 'failed')
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>失败原因</h6></div>
            <div class="card-body">
                <div class="alert alert-danger mb-0">
                    {{ $task->error_message ?? '未知错误' }}
                </div>
                <div class="mt-3">
                    <form method="POST" action="{{ route('price.price-change-tasks.retry', $task) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-refresh me-1"></i>重试任务
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- 状态时间线 --}}
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-clock-o me-2"></i>任务时间线</h6></div>
            <div class="card-body">
                <div class="timeline">
                    <div class="d-flex mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="fa fa-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">任务创建</div>
                            <div class="small text-muted">{{ $task->created_at?->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>

                    @if($task->started_at)
                    <div class="d-flex mb-3">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="fa fa-play"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">开始执行</div>
                            <div class="small text-muted">{{ $task->started_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($task->completed_at)
                    <div class="d-flex">
                        <div class="rounded-circle {{ $task->status === 'failed' ? 'bg-danger' : 'bg-success' }} text-white d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="fa {{ $task->status === 'failed' ? 'fa-times' : 'fa-check' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $task->status === 'failed' ? '执行失败' : '执行完成' }}</div>
                            <div class="small text-muted">{{ $task->completed_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($task->cancelled_at)
                    <div class="d-flex">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="fa fa-ban"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">任务已取消</div>
                            <div class="small text-muted">{{ $task->cancelled_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection