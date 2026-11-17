@extends('layouts.business')

@section('title', '延期审批台')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-hourglass-half"></i>延期审批台</h2>
            <div class="small text-muted">执行人提交的延期申请在此审批；批准后任务结束日期自动顺延</div>
        </div>
        <a href="{{ erp_route('app.todos.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>我的待办</a>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fa-solid fa-inbox me-1"></i>待审批</span>
            <span class="badge bg-warning text-dark">{{ $pending->count() }}</span>
        </div>
        <div class="card-body p-0">
            @forelse($pending as $application)
                <div class="border-bottom p-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">{{ $application->task->name ?? '—' }}</div>
                            <div class="small text-muted">
                                {{ $application->project->name ?? '' }} · 申请人 {{ $application->applicant->name ?? '—' }}
                                · 原截止 {{ $application->task->end_date ?? '—' }} · 申请延期 {{ $application->days }} 天
                            </div>
                            <div class="small mt-1">延期原因：{{ $application->reason }}</div>
                        </div>
                        <div class="d-flex gap-2 align-items-start">
                            <form method="POST" action="{{ erp_route('app.extensions.approve', $application->id) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="审批备注" style="width:150px;">
                                <button class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i>批准</button>
                            </form>
                            <form method="POST" action="{{ erp_route('app.extensions.reject', $application->id) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="驳回原因（必填）" required style="width:160px;">
                                <button class="btn btn-outline-danger btn-sm">驳回</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">没有待审批的延期申请</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-1"></i>已处理</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>任务</th><th>项目</th><th>申请人</th><th>天数</th><th>结果</th><th>审批人</th><th>备注</th></tr></thead>
                <tbody>
                @forelse($handled as $row)
                    <tr>
                        <td class="small">{{ $row->task->name ?? '—' }}</td>
                        <td class="small">{{ $row->project->name ?? '—' }}</td>
                        <td class="small">{{ $row->applicant->name ?? '—' }}</td>
                        <td class="small">{{ $row->days }}</td>
                        <td>
                            <span class="badge bg-{{ $row->status === 'approved' ? 'success' : 'danger' }}-subtle text-{{ $row->status === 'approved' ? 'success' : 'danger' }}-emphasis">
                                {{ $row->status_label }}
                            </span>
                        </td>
                        <td class="small">{{ $row->approver->name ?? '—' }}</td>
                        <td class="small">{{ $row->remark ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">暂无记录</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
