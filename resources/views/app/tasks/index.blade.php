@extends('layouts.business')

@section('title', '任务')

@section('content')
    @php
        $title = match ($mode) {
            'my' => '我的任务',
            'audit' => '我的审核任务',
            default => '任务列表',
        };
        $baseRoute = match ($mode) {
            'my' => 'app.tasks.my-tasks',
            'audit' => 'app.tasks.my-audit-tasks',
            default => 'app.tasks.index',
        };
        $statusRoute = match ($mode) {
            'my' => 'app.tasks.my-tasks.status',
            'audit' => 'app.tasks.my-audit-tasks.status',
            default => null,
        };
        $groups = $groupLabels ?? [];
        $currentGroup = $group ?? 'all';
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-list-check"></i>{{ $title }}</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ erp_route('app.tasks.index') }}" class="btn btn-sm {{ $mode === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">全部任务</a>
            <a href="{{ erp_route('app.tasks.my-tasks') }}" class="btn btn-sm {{ $mode === 'my' ? 'btn-primary' : 'btn-outline-primary' }}">我的任务</a>
            <a href="{{ erp_route('app.tasks.my-audit-tasks') }}" class="btn btn-sm {{ $mode === 'audit' ? 'btn-primary' : 'btn-outline-primary' }}">我的审核任务</a>
        </div>
    </div>

    @if($mode !== 'all')
        <div class="card mb-3">
            <div class="card-body py-2">
                <nav class="nav nav-pills flex-wrap gap-1">
                    <a href="{{ route($baseRoute) }}" class="nav-link {{ $currentGroup === 'all' ? 'active' : '' }}">
                        全部
                        <span class="badge {{ $currentGroup === 'all' ? 'bg-white text-dark' : 'bg-secondary-subtle text-secondary-emphasis' }} ms-1">{{ $counts['all'] ?? 0 }}</span>
                    </a>
                    @foreach($groups as $key => $label)
                        <a href="{{ route($statusRoute, $key) }}" class="nav-link {{ $currentGroup === $key ? 'active' : '' }}">
                            {{ $label }}
                            <span class="badge {{ $currentGroup === $key ? 'bg-white text-dark' : 'bg-secondary-subtle text-secondary-emphasis' }} ms-1">{{ $counts[$key] ?? 0 }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route($baseRoute . ($currentGroup !== 'all' && $statusRoute ? '' : '')) }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">任务名称</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="{{ request('keyword') }}">
                </div>
                @if($mode === 'all')
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="project_id">所属项目</label>
                        <select id="project_id" name="project_id" class="form-select">
                            <option value="">全部项目</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">全部</option>
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-auto">
                    <button class="btn btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>筛选</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>任务</th>
                            <th>所属项目</th>
                            <th>执行人</th>
                            <th>审核人</th>
                            <th>排期</th>
                            <th class="text-center">优先级</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 170px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>
                                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="fw-semibold text-decoration-none">{{ $task->name }}</a>
                                    @if($task->taskType)
                                        <span class="badge bg-light text-muted border ms-1">{{ $task->taskType->name }}</span>
                                    @endif
                                </td>
                                <td class="small">
                                    <a href="{{ erp_route('app.projects.show', $task->project_id) }}" class="text-decoration-none">
                                        {{ $task->project?->name }}
                                    </a>
                                </td>
                                <td>{{ $task->executor?->name ?: '—' }}</td>
                                <td>{{ $task->auditor?->name ?: '—' }}</td>
                                <td class="small">
                                    {{ optional($task->start_date)->format('Y-m-d') ?: '—' }}
                                    ~
                                    {{ optional($task->end_date)->format('Y-m-d') ?: '—' }}
                                    @if($task->is_overdue || $task->status === 'overdue')
                                        <div class="text-danger">逾期 {{ $task->overdueDays() }} 天</div>
                                    @endif
                                    @if($task->extension_days > 0)
                                        <div class="text-warning">已延期 {{ $task->extension_days }} 天</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $task->priority_label }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }}-emphasis border border-{{ $task->status_color }}-subtle">
                                        {{ $task->status_label }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="详情">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($task->canUploadFile())
                                        <a href="{{ erp_route('app.tasks.upload', $task) }}" class="btn btn-sm btn-outline-primary" title="上传交付文件">
                                            <i class="fa-solid fa-upload"></i>
                                        </a>
                                    @endif
                                    @if($task->canAudit())
                                        <a href="{{ erp_route('app.tasks.audit', $task) }}" class="btn btn-sm btn-success" title="审核">
                                            <i class="fa-solid fa-clipboard-check"></i>
                                        </a>
                                    @endif
                                    @if($task->canUpdateFile())
                                        <a href="{{ erp_route('app.tasks.update-file', $task) }}" class="btn btn-sm btn-outline-warning" title="更新文件">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="erp-empty">暂无任务</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">{{ $tasks->links() }}</div>
        </div>
    </div>
@endsection
