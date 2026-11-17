@extends('pm::layouts.app')

@section('title', $project->name.' · 项目详情')

@section('content')
@include('pm::project.partials.tabs', ['project' => $project, 'activeTab' => 'overview'])

<div class="pm-panel">
    <div class="pm-panel-head">
        <h3><i class="fas fa-tachometer" aria-hidden="true"></i>项目概览</h3>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning btn-sm" disabled title="变更项目（B-03 后半接入）">
                <i class="fas fa-edit" aria-hidden="true"></i> 变更项目
            </button>
            <button type="button" class="btn btn-secondary btn-sm" disabled title="暂停项目（B-03 后半接入）">
                <i class="fas fa-pause" aria-hidden="true"></i> 暂停项目
            </button>
        </div>
    </div>
    <div class="pm-panel-body">
        <div class="pm-info-grid mb-3">
            <div class="pm-info-card"><div class="k">项目编码</div><div class="v">{{ $project->code }}</div></div>
            <div class="pm-info-card"><div class="k">产品名称</div><div class="v">{{ $project->product_name ?: '—' }}</div></div>
            <div class="pm-info-card"><div class="k">产品型号</div><div class="v">{{ $project->product_model ?: '—' }}</div></div>
            <div class="pm-info-card"><div class="k">项目状态</div><div class="v">
                <span class="pm-status" style="background:{{ $project->statusColor() }}">{{ $project->statusLabel() }}</span>
            </div></div>
        </div>

        <div class="pm-two-col mb-3">
            <div class="pm-panel m-0">
                <div class="pm-panel-head"><h3><i class="fas fa-users" aria-hidden="true"></i>项目成员</h3></div>
                <div class="pm-panel-body">
                    <ul class="pm-member-list">
                        @forelse ($participants as $member)
                            <li>{{ $member->displayName() }}@if ($member->department) · {{ $member->department->name }}@endif</li>
                        @empty
                            <li class="text-muted">暂无成员</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="pm-panel m-0">
                <div class="pm-panel-head"><h3><i class="fas fa-calendar" aria-hidden="true"></i>时间信息</h3></div>
                <div class="pm-panel-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <tr><th style="width:38%">开始日期</th><td>{{ $project->start_date?->format('Y-m-d') ?: '—' }}</td></tr>
                        <tr><th>结束日期</th><td>{{ $project->end_date?->format('Y-m-d') ?: '—' }}</td></tr>
                        <tr><th>创建人</th><td>{{ $project->creator?->displayName() ?: '—' }}</td></tr>
                        <tr><th>版本号</th><td>{{ $project->version }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pm-panel m-0">
            <div class="pm-panel-head"><h3><i class="fas fa-chart-bar" aria-hidden="true"></i>项目统计</h3></div>
            <div class="pm-panel-body">
                <div class="pm-stat-grid">
                    <div class="pm-stat"><div class="n" style="color:#0d6efd">{{ $stats['total_tasks'] }}</div><div class="l">总任务数</div></div>
                    <div class="pm-stat"><div class="n" style="color:#28a745">{{ $stats['completed_tasks'] }}</div><div class="l">已完成</div></div>
                    <div class="pm-stat"><div class="n" style="color:#ffc107">{{ $stats['pending_tasks'] }}</div><div class="l">待处理</div></div>
                    <div class="pm-stat"><div class="n" style="color:#0dcaf0">{{ $stats['for_audit_tasks'] }}</div><div class="l">待审核</div></div>
                    <div class="pm-stat"><div class="n" style="color:#dc3545">{{ $stats['rejected_tasks'] }}</div><div class="l">已驳回</div></div>
                    <div class="pm-stat"><div class="n" style="color:#fd7e14">{{ $stats['overdue_tasks'] }}</div><div class="l">已逾期</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
