@extends('layouts.business')

@section('title', $project->name)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1">
                <i class="fa-solid fa-diagram-project"></i>{{ $project->name }}
                <span class="badge bg-{{ $project->status_color }}-subtle text-{{ $project->status_color }}-emphasis border border-{{ $project->status_color }}-subtle align-middle">
                    {{ $project->status_label }}
                </span>
                @if($project->isOverdue())
                    <span class="badge bg-danger text-white align-middle">逾期 {{ $project->overdueDays() }} 天</span>
                @endif
            </h2>
            <div class="small text-muted">
                编号 {{ $project->code ?: '—' }}
                @if($project->version)　版本 {{ $project->version }} @endif
                @if($project->product_name)　产品 {{ $project->product_name }} @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ erp_route('app.projects.collab.index', $project) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-file-pen me-1"></i>协同办公
            </a>
            <a href="{{ erp_route('app.projects.voice.index', $project) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-phone-volume me-1"></i>电话会议
            </a>
            <a href="{{ erp_route('app.projects.messages.index', $project) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-comments me-1"></i>项目聊天室
            </a>
            <a href="{{ erp_route('app.files.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-folder-tree me-1"></i>文件中心
            </a>
            <a href="{{ erp_route('app.projects.gantt', $project) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-chart-gantt me-1"></i>甘特图
            </a>
            <a href="{{ erp_route('app.projects.logs', $project) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>项目履历
            </a>
            @can('update', $project)
                <a href="{{ erp_route('app.projects.edit', $project) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-pen me-1"></i>编辑
                </a>
            @endcan
            @can('change', $project)
                <a href="{{ erp_route('app.projects.change', $project) }}" class="btn btn-outline-warning btn-sm">
                    <i class="fa-solid fa-shuffle me-1"></i>变更
                </a>
            @endcan
            @can('pause', $project)
                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#pause-form">
                    <i class="fa-solid fa-pause me-1"></i>暂停
                </button>
            @endcan
            @can('resume', $project)
                <form method="POST" action="{{ erp_route('app.projects.resume', $project) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-success btn-sm"><i class="fa-solid fa-play me-1"></i>恢复</button>
                </form>
            @endcan
            <a href="{{ erp_route('app.projects.index') }}" class="btn btn-light btn-sm">
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

    @can('pause', $project)
        <div class="collapse mb-3" id="pause-form">
            <div class="card border-warning">
                <div class="card-body">
                    <form method="POST" action="{{ erp_route('app.projects.pause', $project) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-12 col-md-8">
                            <label class="form-label">暂停原因</label>
                            <input type="text" name="reason" class="form-control" required placeholder="如：客户需求变更，等待确认">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-warning">确认暂停</button>
                        </div>
                    </form>
                    <div class="small text-muted mt-2">暂停期间任务的文件上传、审核、延期全部冻结；恢复后按新排期继续。</div>
                </div>
            </div>
        </div>
    @endcan

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>项目信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">创建人</div>
                            <div class="fw-semibold">{{ $project->creator?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">负责人</div>
                            <div class="fw-semibold">{{ $project->manager?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">客户</div>
                            <div class="fw-semibold">{{ $project->customer?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">预算</div>
                            <div class="fw-semibold">{{ number_format((float) $project->budget, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">周期</div>
                            <div class="fw-semibold small">
                                {{ optional($project->start_date)->format('Y-m-d') ?: '—' }}
                                ~
                                {{ optional($project->end_date)->format('Y-m-d') ?: '—' }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">已投入工时</div>
                            <div class="fw-semibold">{{ $project->total_hours }} 小时</div>
                        </div>
                        @if($project->status === 'suspended')
                            <div class="col-12">
                                <div class="text-muted small">暂停原因</div>
                                <div class="fw-semibold text-warning">{{ $project->paused_reason ?: '—' }}</div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="text-muted small">项目说明</div>
                            <div>{{ $project->description ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-gauge-high me-2"></i>进度与成员</div>
                <div class="card-body">
                    @php
                        $totalTasks = $project->tasks->count();
                        $doneTasks = $project->tasks->whereIn('status', ['completed', 'overdue_completed'])->count();
                        $auditTasks = $project->tasks->where('status', 'for_audit')->count();
                        $overdueTasks = $project->tasks->filter(fn ($t) => $t->status === 'overdue' || $t->is_overdue)->count();
                    @endphp
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">任务完成度</span>
                        <span class="fw-semibold">{{ (int) $project->progress }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar" style="width: {{ (int) $project->progress }}%"></div>
                    </div>

                    <div class="row g-2 mb-3 text-center">
                        <div class="col-3">
                            <div class="border rounded py-2">
                                <div class="fw-semibold">{{ $totalTasks }}</div>
                                <div class="small text-muted">任务总数</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded py-2">
                                <div class="fw-semibold text-success">{{ $doneTasks }}</div>
                                <div class="small text-muted">已完成</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded py-2">
                                <div class="fw-semibold text-info">{{ $auditTasks }}</div>
                                <div class="small text-muted">待审核</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded py-2">
                                <div class="fw-semibold text-danger">{{ $overdueTasks }}</div>
                                <div class="small text-muted">逾期</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted small mb-2">项目参与人（创建人 · 负责人 · 任务执行人 · 任务审核人）</div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($project->participants() as $member)
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-user me-1"></i>{{ $member->name }}
                            </span>
                        @empty
                            <span class="text-muted small">暂无参与人</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-tasks me-2"></i>任务列表（{{ $project->tasks->count() }}）</span>
            @can('update', $project)
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#add-task-form">
                    <i class="fa-solid fa-plus me-1"></i>添加任务
                </button>
            @endcan
        </div>
        <div class="card-body">
            @can('update', $project)
                <div class="collapse mb-3" id="add-task-form">
                    <form method="POST" action="{{ erp_route('app.projects.tasks.store', $project) }}" class="row g-2 align-items-end border rounded p-2 bg-body-tertiary">
                        @csrf
                        <div class="col-12 col-md-3">
                            <label class="form-label small mb-0">任务名称</label>
                            <input type="text" name="name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-0">类型</label>
                            <select name="task_type_id" class="form-select form-select-sm">
                                <option value="">（未分类）</option>
                                @foreach(\App\Erp\Models\Project\TaskType::orderBy('sort_order')->get() as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-0">执行人</label>
                            <select name="executor_id" class="form-select form-select-sm" required>
                                <option value="">请选择</option>
                                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-0">审核人</label>
                            <select name="auditor_id" class="form-select form-select-sm" required>
                                <option value="">请选择</option>
                                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-1">
                            <label class="form-label small mb-0">优先级</label>
                            <select name="priority" class="form-select form-select-sm">
                                @foreach($priorityLabels as $key => $label)
                                    <option value="{{ $key }}" {{ $key == 2 ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-0">开始</label>
                            <input type="date" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-0">结束</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-md-2 d-grid">
                            <button class="btn btn-sm btn-primary">添加</button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>任务</th>
                            <th class="text-center">优先级</th>
                            <th>执行人</th>
                            <th>审核人</th>
                            <th>排期</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">文件</th>
                            <th class="text-end" style="width: 200px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->tasks as $task)
                            @php($ability = $abilities[$task->id] ?? [])
                            <tr>
                                <td>
                                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="fw-semibold text-decoration-none">
                                        {{ $task->name }}
                                    </a>
                                    @if($task->taskType)
                                        <span class="badge bg-light text-muted border ms-1">{{ $task->taskType->name }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $task->priority_label }}</td>
                                <td>{{ $task->executor?->name ?: '—' }}</td>
                                <td>{{ $task->auditor?->name ?: '—' }}</td>
                                <td class="small">
                                    {{ optional($task->start_date)->format('Y-m-d') ?: '—' }}
                                    ~
                                    {{ optional($task->end_date)->format('Y-m-d') ?: '—' }}
                                    @if($task->extension_days > 0)
                                        <span class="text-warning">（延期 {{ $task->extension_days }} 天）</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }}-emphasis border border-{{ $task->status_color }}-subtle">
                                        {{ $task->status_label }}
                                    </span>
                                </td>
                                <td class="text-center small">
                                    @php($pending = $task->files->where('status', 'pending')->count())
                                    @php($approved = $task->files->where('status', 'approved')->count())
                                    @if($pending > 0)<span class="badge bg-info-subtle text-info-emphasis border">待审 {{ $pending }}</span>@endif
                                    @if($approved > 0)<span class="badge bg-success-subtle text-success-emphasis border">已通过 {{ $approved }}</span>@endif
                                    @if($pending === 0 && $approved === 0)<span class="text-muted">—</span>@endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="任务详情">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($ability['upload'] ?? false)
                                        <a href="{{ erp_route('app.tasks.upload', $task) }}" class="btn btn-sm btn-outline-primary" title="上传交付文件">
                                            <i class="fa-solid fa-upload"></i>
                                        </a>
                                    @endif
                                    @if($ability['audit'] ?? false)
                                        <a href="{{ erp_route('app.tasks.audit', $task) }}" class="btn btn-sm btn-success" title="审核">
                                            <i class="fa-solid fa-clipboard-check"></i>
                                        </a>
                                    @endif
                                    @if($ability['update_file'] ?? false)
                                        <a href="{{ erp_route('app.tasks.update-file', $task) }}" class="btn btn-sm btn-outline-warning" title="更新文件">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </a>
                                    @endif
                                    @can('update', $task)
                                        <form action="{{ erp_route('app.projects.tasks.destroy', $task) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('确定删除任务「{{ $task->name }}」？其工时记录会一并删除')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="删除任务">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="erp-empty">暂无任务，请点击「添加任务」</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-clock me-2"></i>最近工时记录</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr><th>日期</th><th>任务</th><th>登记人</th><th class="text-end">工时</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse($timeEntries as $entry)
                                    <tr>
                                        <td>{{ $entry->date }}</td>
                                        <td class="small">{{ $entry->task?->name }}</td>
                                        <td>{{ $entry->user?->name }}</td>
                                        <td class="text-end">{{ $entry->hours }}</td>
                                        <td class="text-end">
                                            @if($entry->user_id === auth()->id() || auth()->user()->hasRole('admin'))
                                                <form action="{{ erp_route('app.projects.time.destroy', $entry) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('确定删除该工时记录？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash-can"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="erp-empty">暂无工时记录</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-clock-rotate-left me-2"></i>最近履历</span>
                    <a href="{{ erp_route('app.projects.logs', $project) }}" class="small text-decoration-none">查看全部</a>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse($logs as $log)
                            <li class="mb-2 pb-2 border-bottom">
                                <div class="small text-muted">
                                    {{ $log->created_at?->format('Y-m-d H:i') }}
                                    · {{ $log->user?->name ?: '系统' }}
                                    · {{ $log->action_label }}
                                </div>
                                <div class="small">{{ $log->description }}</div>
                            </li>
                        @empty
                            <li class="text-muted small">暂无履历</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
