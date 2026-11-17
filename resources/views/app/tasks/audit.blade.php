@extends('layouts.business')

@section('title', '审核任务')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>审核交付文件</h2>
        <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回任务
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>任务与文件</div>
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-12 col-md-4">
                    <div class="text-muted">任务</div>
                    <div class="fw-semibold">{{ $task->name }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">执行人</div>
                    <div class="fw-semibold">{{ $task->executor?->name ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">审核人</div>
                    <div class="fw-semibold">{{ $task->auditor?->name ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">排期</div>
                    <div class="fw-semibold">
                        {{ optional($task->end_date)->format('Y-m-d') ?: '—' }}
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">任务状态</div>
                    <div class="fw-semibold">{{ $task->status_label }}</div>
                </div>
            </div>

            @if($file)
                <hr>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold">
                            v{{ $file->version }} · {{ $file->original_name }}
                        <span class="badge bg-info-subtle text-info-emphasis border ms-1">{{ $file->status_label }}</span>
                        </div>
                        <div class="small text-muted">
                            上传人 {{ $file->uploader?->name ?: '—' }} ·
                            {{ $file->created_at?->format('Y-m-d H:i') }} ·
                            {{ $file->size_label }}
                        </div>
                    </div>
                    <a href="{{ erp_route('app.tasks.files.download', [$task, $file]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-download me-1"></i>下载查看
                    </a>
                </div>
            @else
                <div class="alert alert-light border mb-0 mt-3">当前没有待审核的文件</div>
            @endif
        </div>
    </div>

    @if($file)
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-header text-success"><i class="fa-solid fa-check me-2"></i>审核通过</div>
                    <div class="card-body">
                        <form method="POST" action="{{ erp_route('app.tasks.audit.approve', $task) }}">
                            @csrf
                            <label class="form-label" for="approve-remark">审核意见（可选）</label>
                            <textarea id="approve-remark" name="remark" rows="3" class="form-control"></textarea>
                            <div class="form-text">通过后任务将直接标记为已完成（原本已逾期的记为「逾期完成」）。</div>
                            <button class="btn btn-success mt-2 w-100">
                                <i class="fa-solid fa-check me-1"></i>确认通过
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card h-100 border-danger">
                    <div class="card-header text-danger"><i class="fa-solid fa-xmark me-2"></i>退回修改</div>
                    <div class="card-body">
                        <form method="POST" action="{{ erp_route('app.tasks.audit.reject', $task) }}">
                            @csrf
                            <label class="form-label" for="reject-remark">退回原因 <span class="text-danger">*</span></label>
                            <textarea id="reject-remark" name="remark" rows="3" class="form-control" required
                                      placeholder="如：图纸缺少公差标注，请补充后重新提交"></textarea>
                            <div class="form-text">退回后文件状态变为「已驳回」，执行人可重新上传。</div>
                            <button class="btn btn-danger mt-2 w-100">
                                <i class="fa-solid fa-xmark me-1"></i>确认退回
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
