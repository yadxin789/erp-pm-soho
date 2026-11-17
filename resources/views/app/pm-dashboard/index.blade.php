@extends('layouts.business')

@section('title', '项目仪表盘')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-gauge-high"></i>项目仪表盘</h2>
            <div class="small text-muted">只统计我参与的项目</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.projects.index') }}" class="btn btn-outline-secondary btn-sm">项目列表</a>
            <a href="{{ erp_route('app.todos.index') }}" class="btn btn-outline-secondary btn-sm">我的待办</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach([
            ['参与项目', $cards['projects_total'], 'primary', 'fa-diagram-project'],
            ['进行中', $cards['in_progress'], 'success', 'fa-play'],
            ['立项中', $cards['pending_start'], 'secondary', 'fa-hourglass-start'],
            ['已暂停', $cards['suspend'], 'warning', 'fa-pause'],
            ['已完成', $cards['completed'], 'info', 'fa-flag-checkered'],
        ] as $card)
            <div class="col-md">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted">{{ $card[0] }}</div>
                                <div class="fs-3 fw-semibold">{{ $card[1] }}</div>
                            </div>
                            <i class="fa-solid {{ $card[3] }} fa-2x text-{{ $card[2] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-chart-pie me-1"></i>任务状态分布</div>
                <div class="card-body">
                    @php
                        $total = max(1, array_sum($taskStats));
                        $meta = [
                            'pending' => ['待开始', 'secondary'],
                            'in_progress' => ['进行中', 'primary'],
                            'for_audit' => ['待审核', 'warning'],
                            'completed' => ['已完成', 'success'],
                            'overdue' => ['逾期', 'danger'],
                            'rejected' => ['已退回', 'dark'],
                        ];
                    @endphp
                    @foreach($meta as $key => [$label, $color])
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $label }}</span>
                            <span>{{ $taskStats[$key] }} 个</span>
                        </div>
                        <div class="progress mb-2" style="height:8px;">
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ round(($taskStats[$key] / $total) * 100) }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-user-check me-1"></i>我的工作量</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-muted">我执行的任务</div>
                        <div class="fs-4 fw-semibold">{{ $mine['executor'] }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">我审核的任务</div>
                        <div class="fs-4 fw-semibold">{{ $mine['auditor'] }}</div>
                    </div>
                    <div>
                        <div class="small text-muted">待我审批延期</div>
                        <div class="fs-4 fw-semibold">{{ $pendingExtensions }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-users me-1"></i>成员任务负荷</div>
                <div class="list-group list-group-flush">
                    @forelse($memberLoad as $row)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between small">
                                <span>{{ $row['name'] }}</span>
                                <span class="text-muted">{{ $row['done'] }}/{{ $row['total'] }}</span>
                            </div>
                            <div class="progress mt-1" style="height:6px;">
                                <div class="progress-bar" style="width: {{ $row['total'] ? round(($row['done'] / $row['total']) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item small text-muted">暂无</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-1"></i>最近动态</div>
        <div class="list-group list-group-flush">
            @forelse($recentLogs as $log)
                <div class="list-group-item d-flex justify-content-between small">
                    <span>{{ $log->project->name ?? '' }} · {{ $log->action_label }}{{ $log->description ? '：' . $log->description : '' }}</span>
                    <span class="text-muted">{{ $log->user->name ?? '' }} {{ optional($log->created_at)->format('m-d H:i') }}</span>
                </div>
            @empty
                <div class="list-group-item small text-muted">暂无</div>
            @endforelse
        </div>
    </div>
@endsection
