@extends('layouts.business')

@section('title', $task->name)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="page-title mb-1">
                <i class="fa-solid fa-list-check"></i>{{ $task->name }}
                <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }}-emphasis border border-{{ $task->status_color }}-subtle align-middle">
                    {{ $task->status_label }}
                </span>
            </h2>
            <div class="small text-muted">
                所属项目：
                <a href="{{ erp_route('app.projects.show', $task->project_id) }}" class="text-decoration-none">
                    {{ $task->project?->name }}
                </a>
                @if($task->taskType)　类型 {{ $task->taskType->name }} @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($canOperate)
                <form method="POST" action="{{ erp_route('app.tasks.start', $task) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-play me-1"></i>开始任务</button>
                </form>
            @endif
            @if($canUpload)
                <a href="{{ erp_route('app.tasks.upload', $task) }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-upload me-1"></i>上传交付文件
                </a>
            @endif
            @if($canAudit)
                <a href="{{ erp_route('app.tasks.audit', $task) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-clipboard-check me-1"></i>审核
                </a>
            @endif
            @if($canUpdateFile)
                <a href="{{ erp_route('app.tasks.update-file', $task) }}" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>更新文件
                </a>
            @endif
            @if($canOperate && $task->status !== 'completed')
                <button class="btn btn-outline-warning btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#extension-form">
                    <i class="fa-solid fa-hourglass-half me-1"></i>申请延期
                </button>
            @endif
            <a href="{{ erp_route('app.tasks.index') }}" class="btn btn-light btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($canOperate && $task->status !== 'completed')
        <div class="collapse mb-3" id="extension-form">
            <div class="card border-warning">
                <div class="card-header bg-warning-subtle">申请延期</div>
                <div class="card-body">
                    <form method="POST" action="{{ erp_route('app.tasks.store-extension', $task) }}">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small">延期天数 <span class="text-danger">*</span></label>
                                <input type="number" name="days" class="form-control" min="1" max="365" value="3" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small">延期原因 <span class="text-danger">*</span></label>
                                <input type="text" name="reason" class="form-control" required placeholder="说明延期原因，提交后由审核人审批">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-warning">提交申请</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>任务信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">任务执行人</div>
                            <div class="fw-semibold">{{ $task->executor?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">任务审核人</div>
                            <div class="fw-semibold">{{ $task->auditor?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">优先级</div>
                            <div class="fw-semibold">{{ $task->priority_label }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">累计工时</div>
                            <div class="fw-semibold">{{ $task->total_hours }} 小时</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">计划排期</div>
                            <div class="fw-semibold small">
                                {{ optional($task->start_date)->format('Y-m-d') ?: '—' }}
                                ~
                                {{ optional($task->end_date)->format('Y-m-d') ?: '—' }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">延期 / 逾期</div>
                            <div class="fw-semibold small">
                                延期 {{ $task->extension_days }} 天
                                @if($task->is_overdue || $task->status === 'overdue')
                                    <span class="text-danger">／逾期 {{ $task->overdueDays() }} 天</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">任务说明</div>
                            <div>{{ $task->description ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-clock me-2"></i>登记工时</div>
                <div class="card-body">
                    <form method="POST" action="{{ erp_route('app.projects.tasks.time.store', $task) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-6 col-md-4">
                            <label class="form-label small mb-0">日期</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small mb-0">工时</label>
                            <input type="number" step="0.1" name="hours" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small mb-0">说明</label>
                            <input type="text" name="description" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 d-grid">
                            <button class="btn btn-sm btn-success">登记</button>
                        </div>
                    </form>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>日期</th><th>登记人</th><th class="text-end">工时</th><th>说明</th></tr></thead>
                            <tbody>
                                @forelse($task->timeEntries as $entry)
                                    <tr>
                                        <td class="small">{{ $entry->date }}</td>
                                        <td class="small">{{ $entry->user?->name }}</td>
                                        <td class="text-end">{{ $entry->hours }}</td>
                                        <td class="small text-muted">{{ $entry->description ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="erp-empty">暂无工时</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-paperclip me-2"></i>交付文件与审核记录（{{ $files->count() }}）
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">版本</th>
                            <th>文件名</th>
                            <th class="text-center">大小</th>
                            <th class="text-center">状态</th>
                            <th>上传人</th>
                            <th>上传时间</th>
                            <th>审核人 / 意见</th>
                            <th class="text-end" style="width: 90px;">下载</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $file)
                            <tr>
                                <td class="text-center">v{{ $file->version }}</td>
                                <td class="small">{{ $file->original_name }}</td>
                                <td class="text-center small">{{ $file->size_label }}</td>
                                <td class="text-center">
                                    @php
                                        $fileBadge = ['pending' => 'info', 'approved' => 'success', 'rejected' => 'danger'][$file->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $fileBadge }}-subtle text-{{ $fileBadge }}-emphasis border border-{{ $fileBadge }}-subtle">
                                        {{ $file->status_label }}
                                    </span>
                                </td>
                                <td class="small">{{ $file->uploader?->name ?: '—' }}</td>
                                <td class="small text-muted">{{ $file->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="small">
                                    {{ $file->auditor?->name ?: '—' }}
                                    @if($file->audit_remark)
                                        <div class="text-muted">{{ $file->audit_remark }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ erp_route('app.tasks.files.download', [$task, $file]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="erp-empty">暂无文件</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(! $canUpload && ! $canAudit && ! $canUpdateFile)
                <div class="alert alert-light border small mb-0 mt-3">
                    当前你对该任务没有可执行的文件操作：上传仅限任务执行人，审核仅限任务审核人（且不能自审），
                    更新文件仅限执行人 / 项目创建人；项目暂停期间全部冻结。
                </div>
            @endif
        </div>
    </div>
    @if(isset($extensions) && $extensions->count())
        <div class="card mt-3">
            <div class="card-header"><i class="fa-solid fa-hourglass-half me-1"></i>延期记录</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                    <tr><th>申请时间</th><th>申请人</th><th>天数</th><th>原因</th><th>状态</th><th>审批人</th><th>备注</th></tr>
                    </thead>
                    <tbody>
                    @foreach($extensions as $row)
                        <tr>
                            <td class="small">{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="small">{{ $row->applicant->name ?? '—' }}</td>
                            <td class="small">{{ $row->days }}</td>
                            <td class="small">{{ $row->reason }}</td>
                            <td class="small">{{ $row->status_label }}</td>
                            <td class="small">{{ $row->approver->name ?? '—' }}</td>
                            <td class="small">{{ $row->remark ?: '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
