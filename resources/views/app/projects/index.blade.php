@extends('layouts.business')

@section('title', $isMine ? '我的项目' : '项目管理')

@section('content')
    @php
        $tabs = [
            ['key' => 'all', 'label' => '全部', 'href' => $isMine ? erp_route('app.projects.my-projects') : erp_route('app.projects.index')],
        ];
        foreach ($statusLabels as $key => $label) {
            $tabs[] = [
                'key' => $key,
                'label' => $label,
                'href' => $isMine
                    ? erp_route('app.projects.my-projects.status', $key)
                    : erp_route('app.projects.index', ['status' => $key]),
            ];
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title">
            <i class="fa-solid fa-diagram-project"></i>{{ $isMine ? '我的项目' : '产品研发项目' }}
        </h2>
        <div class="d-flex gap-2">
            @if(! $isMine)
                <a href="{{ erp_route('app.projects.my-projects') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-user-check me-1"></i>我的项目
                </a>
            @endif
            @can('create', \App\Erp\Models\Project\Project::class)
                <a href="{{ erp_route('app.projects.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>新建项目
                </a>
            @endcan
        </div>
    </div>

    {{-- 状态页标签 --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <nav class="nav nav-pills flex-wrap gap-1 erp-tabs">
                @foreach($tabs as $tab)
                    <a href="{{ $tab['href'] }}"
                       class="nav-link {{ $activeStatus === $tab['key'] ? 'active' : '' }}">
                        {{ $tab['label'] }}
                        <span class="badge {{ $activeStatus === $tab['key'] ? 'bg-white text-dark' : 'bg-secondary-subtle text-secondary-emphasis' }} ms-1">
                            {{ $counts[$tab['key']] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ $isMine ? erp_route('app.projects.my-projects') : erp_route('app.projects.index') }}"
                  class="row g-2 align-items-end">
                @if(! $isMine && $activeStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">项目名称 / 编号 / 产品</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="{{ request('keyword') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>筛选
                    </button>
                    <a href="{{ $isMine ? erp_route('app.projects.my-projects') : erp_route('app.projects.index') }}"
                       class="btn btn-light">重置</a>
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
                            <th style="width: 90px;">编号</th>
                            <th>项目名称</th>
                            <th>产品 / 版本</th>
                            <th>负责人</th>
                            <th class="text-center">任务</th>
                            <th>周期</th>
                            <th style="width: 140px;">进度</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 130px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td class="small text-muted">{{ $project->code ?: '—' }}</td>
                                <td class="fw-semibold">
                                    <a href="{{ erp_route('app.projects.show', $project) }}" class="text-decoration-none">
                                        {{ $project->name }}
                                    </a>
                                    @if($project->isOverdue())
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle ms-1">
                                            逾期 {{ $project->overdueDays() }} 天
                                        </span>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ $project->product_name ?: '—' }}
                                    @if($project->version)
                                        <span class="text-muted">/ {{ $project->version }}</span>
                                    @endif
                                </td>
                                <td>{{ $project->manager?->name ?: '—' }}</td>
                                <td class="text-center">{{ $project->tasks_count }}</td>
                                <td class="small">
                                    {{ optional($project->start_date)->format('Y-m-d') ?: '—' }}
                                    ~
                                    {{ optional($project->end_date)->format('Y-m-d') ?: '—' }}
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px;" role="progressbar"
                                         aria-valuenow="{{ (int) $project->progress }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: {{ (int) $project->progress }}%"></div>
                                    </div>
                                    <div class="small text-muted mt-1">{{ (int) $project->progress }}%</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $project->status_color }}-subtle text-{{ $project->status_color }}-emphasis border border-{{ $project->status_color }}-subtle">
                                        {{ $project->status_label }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-sm btn-outline-secondary" title="详情">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('update', $project)
                                        <a href="{{ erp_route('app.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="erp-empty">暂无项目</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
@endsection
