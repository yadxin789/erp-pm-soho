@extends('layouts.business')

@section('title', '我的待办')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-list-check"></i>我的待办</h2>
            <div class="small text-muted">按任务截止时间排序，只显示与我相关的项目</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.tasks.my-tasks') }}" class="btn btn-outline-secondary btn-sm">我的任务</a>
            <a href="{{ erp_route('app.tasks.my-audit-tasks') }}" class="btn btn-outline-secondary btn-sm">我的审核任务</a>
            <a href="{{ erp_route('app.extensions.index') }}" class="btn btn-outline-secondary btn-sm">延期审批台</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="fa-solid fa-person-digging me-1"></i>我负责的任务</span>
                    <span class="badge bg-secondary">{{ $myTasks->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($myTasks as $task)
                        <a href="{{ erp_route('app.tasks.show', $task) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <span class="fw-semibold small">{{ $task->name }}</span>
                                <div class="text-muted small">{{ $task->project->name ?? '' }} · 截止 {{ $task->end_date ?: '未设置' }}</div>
                            </span>
                            <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }}-emphasis">{{ $task->status_label }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted small">暂无</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="fa-solid fa-clipboard-check me-1"></i>待我审核</span>
                    <span class="badge bg-warning">{{ $auditTasks->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($auditTasks as $task)
                        <a href="{{ erp_route('app.tasks.audit', $task) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <span class="fw-semibold small">{{ $task->name }}</span>
                                <div class="text-muted small">{{ $task->project->name ?? '' }} · 执行人 {{ $task->executor->name ?? '—' }}</div>
                            </span>
                            <span class="badge bg-warning text-dark">待审核</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted small">暂无</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="fa-solid fa-hourglass-half me-1"></i>待我审批的延期</span>
                    <span class="badge bg-secondary">{{ $extensions->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @forelse($extensions as $application)
                        <div class="border-bottom p-3">
                            <div class="small fw-semibold">{{ $application->task->name ?? '—' }}（{{ $application->project->name ?? '' }}）</div>
                            <div class="small text-muted">申请人 {{ $application->applicant->name ?? '—' }} · 延期 {{ $application->days }} 天</div>
                            <div class="small mb-2">原因：{{ $application->reason }}</div>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ erp_route('app.todos.extension.approve', $application->id) }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i>批准</button>
                                </form>
                                <form method="POST" action="{{ erp_route('app.todos.extension.reject', $application->id) }}" class="d-flex gap-1">
                                    @csrf
                                    <input type="text" name="remark" class="form-control form-control-sm" placeholder="驳回原因" required style="width:140px;">
                                    <button class="btn btn-outline-danger btn-sm">驳回</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-muted small">暂无待审批延期</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fa-solid fa-bell me-1"></i>系统待办 / 通知</div>
                <div class="list-group list-group-flush">
                    @forelse($customs as $todo)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">{{ $todo->title }}</span>
                            <form method="POST" action="{{ erp_route('app.todos.complete', $todo->id) }}">
                                @csrf
                                <button class="btn btn-outline-success btn-sm"><i class="fa-solid fa-check"></i></button>
                            </form>
                        </div>
                    @empty
                        <div class="list-group-item text-muted small">暂无</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
