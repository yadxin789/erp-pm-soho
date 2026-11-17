@extends('core::layouts.base')

@section('title', '审批详情 · '.config('app.name'))

@section('content')
@include('core::partials.topbar')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">{{ strtoupper($instance->module) }} · {{ $instance->flow?->name }}</p>
            <h1 class="dw-page-title">{{ $instance->title }}</h1>
            <p class="dw-page-desc">
                单据 <code>{{ $instance->document_type }}#{{ $instance->document_id }}</code>
                · 发起人 {{ $instance->applicant?->displayName() }}
                · 提交于 {{ $instance->submitted_at?->format('Y-m-d H:i') }}
                · <span class="dw-badge dw-badge--{{ $instance->statusTone() }}">{{ $instance->statusLabel() }}</span>
            </p>
        </div>
        <div class="dw-page-actions">
            <a class="dw-btn" href="{{ route('core.approvals.index') }}">返回列表</a>
            @if ($canCancel)
                <form method="POST" action="{{ route('core.approvals.cancel', $instance->id) }}"
                      onsubmit="return confirm('撤销后这张单据需要重新提交审批，确定撤销？');">
                    @csrf
                    <button type="submit" class="dw-btn dw-btn-danger">撤销审批</button>
                </form>
            @endif
        </div>
    </header>

    @if (session('success'))
        <section class="dw-card dw-alert dw-alert-ok" role="status"><p class="dw-hint">{{ session('success') }}</p></section>
    @endif
    @if (session('error'))
        <section class="dw-card dw-alert" role="alert"><p class="dw-hint">{{ session('error') }}</p></section>
    @endif

    <section class="dw-card">
        <h2 class="dw-card-title">审批进度</h2>
        <ul class="dw-plain-list">
            @foreach ($instance->flow->steps as $step)
                @php
                    $stepTasks = $instance->tasks->where('step_order', $step->step_order);
                    $done = $stepTasks->first(fn ($t) => $t->status === \App\Core\Models\ApprovalTask::STATUS_APPROVED);
                    $rejected = $stepTasks->first(fn ($t) => $t->status === \App\Core\Models\ApprovalTask::STATUS_REJECTED);
                    $isCurrent = $instance->isPending() && $instance->current_step === $step->step_order;
                @endphp
                <li>
                    @if ($done)
                        <span class="dw-badge dw-badge--ok">已通过</span>
                    @elseif ($rejected)
                        <span class="dw-badge dw-badge--danger">已驳回</span>
                    @elseif ($isCurrent)
                        <span class="dw-badge dw-badge--warn">进行中</span>
                    @else
                        <span class="dw-badge dw-badge--muted">未开始</span>
                    @endif

                    <strong>第 {{ $step->step_order }} 步 · {{ $step->name }}</strong>
                    <span class="dw-hint">审批人：{{ $step->typeLabel() }} {{ $step->approver_value }}</span>

                    @foreach ($stepTasks as $task)
                        <span class="dw-hint">
                            {{ $task->approver?->displayName() }}：
                            {{ $task->statusLabel() }}
                            @if ($task->approved_at)（{{ $task->approved_at->format('Y-m-d H:i') }}）@endif
                            @if ($task->comment)「{{ $task->comment }}」@endif
                        </span>
                    @endforeach
                </li>
            @endforeach
        </ul>
    </section>

    @if ($myTask)
        <section class="dw-card" style="margin-top:18px">
            <h2 class="dw-card-title">轮到我处理：{{ $myTask->step_name }}</h2>
            <form method="POST" action="{{ route('core.approvals.approve', ['approval' => $instance->id, 'task' => $myTask->id]) }}">
                @csrf
                <div class="dw-field">
                    <label for="comment">审批意见<span class="dw-help">（选填，最多 500 字）</span></label>
                    <textarea class="dw-input" id="comment" name="comment" rows="3" maxlength="500"
                              placeholder="例如：价格与合同一致，同意。"></textarea>
                </div>
                <div class="dw-page-actions">
                    <button type="submit" class="dw-btn">同意</button>
                    <button type="submit" class="dw-btn dw-btn-danger"
                            formaction="{{ route('core.approvals.reject', ['approval' => $instance->id, 'task' => $myTask->id]) }}">
                        驳回
                    </button>
                </div>
            </form>
        </section>
    @elseif ($instance->isPending())
        <section class="dw-card dw-card-flat" style="margin-top:18px">
            <p class="dw-hint">这条审批正在第 {{ $instance->current_step }} 步，你当前没有待处理的节点。</p>
        </section>
    @endif

    @if (! empty($instance->payload))
        <section class="dw-card" style="margin-top:18px">
            <h2 class="dw-card-title">单据摘要</h2>
            <ul class="dw-plain-list">
                @foreach ($instance->payload as $key => $value)
                    <li><code>{{ $key }}</code><span>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</span></li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
