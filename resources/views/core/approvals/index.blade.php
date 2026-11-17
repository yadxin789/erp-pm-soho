@extends('core::layouts.base')

@section('title', '审批中心 · '.config('app.name'))

@section('content')
<div class="dw-page">
    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-check-circle" aria-hidden="true"></i></span>
                <h2>审批中心
                    <span class="sub">当前模块 {{ strtoupper($module) }} · 待我处理 {{ $todo->count() }} 条 · 我发起 {{ $mine->count() }} 条</span>
                </h2>
            </div>
            <div class="tools">
                <a class="dw-btn dw-btn-primary" href="{{ route('core.approvals.create') }}"><i class="fas fa-plus" aria-hidden="true"></i> 发起审批</a>
                <a class="dw-btn" href="{{ route('core.notifications.index') }}"><i class="fas fa-bell" aria-hidden="true"></i> 站内通知</a>
            </div>
        </div>

        <div class="dw-panel-body" style="border-bottom:1px solid var(--dw-line);padding:12px 18px">
            <div class="dw-toolbar">
                <a class="dw-chip {{ $tab === 'todo' ? 'is-current' : '' }}" href="{{ route('core.approvals.index', ['tab' => 'todo']) }}">
                    待我审批 <span class="n">{{ $todo->count() }}</span>
                </a>
                <a class="dw-chip {{ $tab === 'mine' ? 'is-current' : '' }}" href="{{ route('core.approvals.index', ['tab' => 'mine']) }}">
                    我发起的 <span class="n">{{ $mine->count() }}</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="dw-panel-body" style="padding:10px 18px;background:#f2fbf5;border-bottom:1px solid #d8f0e0">
                <span class="dw-badge dw-badge--ok">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="dw-panel-body" style="padding:10px 18px;background:#fdf3f4;border-bottom:1px solid #f6dcdf">
                <span class="dw-badge dw-badge--danger">{{ session('error') }}</span>
            </div>
        @endif

        @if ($tab === 'todo')
            @if ($todo->isEmpty())
                <div class="dw-empty-panel">
                    <i class="far fa-check-circle" aria-hidden="true"></i>
                    <p class="t">当前模块没有等着你处理的审批</p>
                    <p class="mb-0">单据提交后会出现在这里；也可以先看看“我发起的”。</p>
                </div>
            @else
                <table class="dw-table dw-table-flush">
                    <caption class="dw-sr">待我处理的审批</caption>
                    <thead>
                        <tr>
                            <th scope="col">单据</th>
                            <th scope="col">标题</th>
                            <th scope="col">发起人</th>
                            <th scope="col">当前步骤</th>
                            <th scope="col">提交时间</th>
                            <th scope="col" style="width:96px">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($todo as $task)
                        <tr>
                            <td data-label="单据"><code style="white-space:nowrap">{{ $task->document_type }}#{{ $task->document_id }}</code></td>
                            <td data-label="标题"><a href="{{ route('core.approvals.show', $task->approval_instance_id) }}">{{ $task->instance?->title }}</a></td>
                            <td data-label="发起人">{{ $task->instance?->applicant?->displayName() ?? '—' }}</td>
                            <td data-label="当前步骤">
                                <span class="dw-badge dw-badge--warn">{{ $task->step_name }}</span>
                                <span class="dw-hint">第 {{ $task->step_order }} 步</span>
                            </td>
                            <td data-label="提交时间">{{ $task->instance?->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td data-label="操作"><a class="dw-btn" style="white-space:nowrap" href="{{ route('core.approvals.show', $task->approval_instance_id) }}">去处理</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @else
            @if ($mine->isEmpty())
                <div class="dw-empty-panel">
                    <i class="far fa-file-alt" aria-hidden="true"></i>
                    <p class="t">还没有发起过审批</p>
                    <p class="mb-0">点右上角“发起审批”，选一条当前模块的审批流。</p>
                </div>
            @else
                <table class="dw-table dw-table-flush">
                    <caption class="dw-sr">我发起的审批</caption>
                    <thead>
                        <tr>
                            <th scope="col">单据</th>
                            <th scope="col">标题</th>
                            <th scope="col">流程</th>
                            <th scope="col">进度</th>
                            <th scope="col">状态</th>
                            <th scope="col">提交 / 结束</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($mine as $item)
                        <tr>
                            <td data-label="单据"><code style="white-space:nowrap">{{ $item->document_type }}#{{ $item->document_id }}</code></td>
                            <td data-label="标题"><a href="{{ route('core.approvals.show', $item->id) }}">{{ $item->title }}</a></td>
                            <td data-label="流程">{{ $item->flow?->name }}</td>
                            <td data-label="进度">第 {{ $item->current_step }} / {{ $item->flow?->steps->count() ?? 0 }} 步</td>
                            <td data-label="状态"><span class="dw-badge dw-badge--{{ $item->statusTone() }}">{{ $item->statusLabel() }}</span></td>
                            <td data-label="提交 / 结束">
                                <div class="dw-hint">{{ $item->submitted_at?->format('Y-m-d H:i') ?? '—' }}</div>
                                <div class="dw-hint">{{ $item->finished_at?->format('Y-m-d H:i') ?? '进行中' }}</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>

    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-history" aria-hidden="true"></i></span>
                <h2>本模块最近完成的审批 <span class="sub">{{ $finished->count() }} 条</span></h2>
            </div>
        </div>
        @if ($finished->isEmpty())
            <div class="dw-empty-panel" style="padding:30px 18px">
                <p class="t mb-1">还没有已结束的审批</p>
                <p class="mb-0">流程走完的单据会留在这里备查。</p>
            </div>
        @else
            <table class="dw-table dw-table-flush">
                <thead><tr><th>标题</th><th>流程</th><th>发起人</th><th>状态</th><th style="width:150px">结束时间</th></tr></thead>
                <tbody>
                @foreach ($finished as $item)
                    <tr>
                        <td data-label="标题"><a href="{{ route('core.approvals.show', $item->id) }}">{{ $item->title }}</a></td>
                        <td data-label="流程">{{ $item->flow?->name }}</td>
                        <td data-label="发起人">{{ $item->applicant?->displayName() }}</td>
                        <td data-label="状态"><span class="dw-badge dw-badge--{{ $item->statusTone() }}">{{ $item->statusLabel() }}</span></td>
                        <td data-label="结束时间">{{ $item->finished_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
