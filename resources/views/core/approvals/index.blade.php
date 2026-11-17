@extends('core::layouts.base')

@section('title', '审批中心 · '.config('app.name'))

@section('content')
@include('core::partials.topbar')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">三模块共用 · 一套审批引擎</p>
            <h1 class="dw-page-title">审批中心</h1>
            <p class="dw-page-desc">
                当前模块 {{ strtoupper($module) }} · 待我处理 {{ $todo->count() }} 条 · 我发起的 {{ $mine->count() }} 条
            </p>
        </div>
        <div class="dw-page-actions">
            <a class="dw-btn" href="{{ route('core.approvals.create') }}">发起审批</a>
            <a class="dw-btn" href="{{ route('core.notifications.index') }}">站内通知</a>
        </div>
    </header>

    @if (session('success'))
        <section class="dw-card dw-alert dw-alert-ok" role="status"><p class="dw-hint">{{ session('success') }}</p></section>
    @endif
    @if (session('error'))
        <section class="dw-card dw-alert" role="alert"><p class="dw-hint">{{ session('error') }}</p></section>
    @endif

    <nav class="dw-page-actions dw-tabs" aria-label="审批视图切换">
        <a class="dw-btn {{ $tab === 'todo' ? 'is-current' : '' }}" href="{{ route('core.approvals.index', ['tab' => 'todo']) }}">
            待我审批 <span class="dw-badge dw-badge--warn">{{ $todo->count() }}</span>
        </a>
        <a class="dw-btn {{ $tab === 'mine' ? 'is-current' : '' }}" href="{{ route('core.approvals.index', ['tab' => 'mine']) }}">
            我发起的 <span class="dw-badge dw-badge--muted">{{ $mine->count() }}</span>
        </a>
    </nav>

    @if ($tab === 'todo')
        @if ($todo->isEmpty())
            <section class="dw-card dw-empty">
                <p class="title">当前模块没有等着你处理的审批</p>
                <p>单据提交后会出现在这里；也可以先看看“我发起的”。</p>
            </section>
        @else
            <section class="dw-card dw-card-flat">
                <table class="dw-table">
                    <caption class="dw-sr">待我处理的审批</caption>
                    <thead>
                        <tr>
                            <th scope="col">单据</th>
                            <th scope="col">标题</th>
                            <th scope="col">发起人</th>
                            <th scope="col">当前步骤</th>
                            <th scope="col">提交时间</th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($todo as $task)
                        <tr>
                            <td><code style="white-space:nowrap">{{ $task->document_type }}#{{ $task->document_id }}</code></td>
                            <td>
                                <a href="{{ route('core.approvals.show', $task->approval_instance_id) }}">{{ $task->instance?->title }}</a>
                            </td>
                            <td>{{ $task->instance?->applicant?->displayName() ?? '—' }}</td>
                            <td>
                                <span class="dw-badge dw-badge--warn">{{ $task->step_name }}</span>
                                <span class="dw-hint">第 {{ $task->step_order }} 步</span>
                            </td>
                            <td>{{ $task->instance?->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td><a class="dw-btn" style="white-space:nowrap" href="{{ route('core.approvals.show', $task->approval_instance_id) }}">去处理</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    @else
        @if ($mine->isEmpty())
            <section class="dw-card dw-empty">
                <p class="title">还没有发起过审批</p>
                <p>点右上角“发起审批”，选一条当前模块的审批流。</p>
            </section>
        @else
            <section class="dw-card dw-card-flat">
                <table class="dw-table">
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
                            <td><code style="white-space:nowrap">{{ $item->document_type }}#{{ $item->document_id }}</code></td>
                            <td><a href="{{ route('core.approvals.show', $item->id) }}">{{ $item->title }}</a></td>
                            <td>{{ $item->flow?->name }}</td>
                            <td>
                                第 {{ $item->current_step }} / {{ $item->flow?->steps->count() ?? 0 }} 步
                            </td>
                            <td><span class="dw-badge dw-badge--{{ $item->statusTone() }}">{{ $item->statusLabel() }}</span></td>
                            <td>
                                <div class="dw-hint">{{ $item->submitted_at?->format('Y-m-d H:i') ?? '—' }}</div>
                                <div class="dw-hint">{{ $item->finished_at?->format('Y-m-d H:i') ?? '进行中' }}</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    @endif

    <section class="dw-card" style="margin-top:18px">
        <h2 class="dw-card-title">本模块最近完成的审批</h2>
        @if ($finished->isEmpty())
            <p class="dw-hint">还没有已结束的审批。</p>
        @else
            <ul class="dw-plain-list">
                @foreach ($finished as $item)
                    <li>
                        <a href="{{ route('core.approvals.show', $item->id) }}">{{ $item->title }}</a>
                        <span class="dw-badge dw-badge--{{ $item->statusTone() }}">{{ $item->statusLabel() }}</span>
                        <span class="dw-hint">{{ $item->flow?->name }} · 发起人 {{ $item->applicant?->displayName() }}</span>
                        <span class="dw-hint">{{ $item->finished_at?->format('Y-m-d H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
