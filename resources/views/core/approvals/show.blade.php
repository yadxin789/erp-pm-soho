@extends('core::layouts.base')

@section('title', '审批详情 · '.config('app.name'))

@section('content')
<div class="dw-page">
    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-check-circle" aria-hidden="true"></i></span>
                <h2>{{ $instance->title }}
                    <span class="sub">{{ strtoupper($instance->module) }} · {{ $instance->flow?->name }} · 单据 {{ $instance->document_type }}#{{ $instance->document_id }}</span>
                </h2>
                <span class="dw-badge dw-badge--{{ $instance->statusTone() }}">{{ $instance->statusLabel() }}</span>
            </div>
            <div class="tools">
                <a class="dw-btn" href="{{ route('core.approvals.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> 返回列表</a>
                @if ($canCancel)
                    <form method="POST" action="{{ route('core.approvals.cancel', $instance->id) }}" class="dw-inline-form"
                          onsubmit="return confirm('撤销后这张单据需要重新提交审批，确定撤销？');">
                        @csrf
                        <button type="submit" class="dw-btn dw-btn-danger"><i class="fas fa-ban" aria-hidden="true"></i> 撤销审批</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="dw-panel-body" style="padding:10px 18px;color:var(--dw-muted);font-size:13px">
            发起人 {{ $instance->applicant?->displayName() }} · 提交于 {{ $instance->submitted_at?->format('Y-m-d H:i') }}
            @if ($instance->finished_at) · 结束于 {{ $instance->finished_at->format('Y-m-d H:i') }} @endif
        </div>
    </div>

    @if (session('success'))
        <section class="dw-card dw-alert dw-alert-ok" role="status"><p class="dw-hint">{{ session('success') }}</p></section>
    @endif
    @if (session('error'))
        <section class="dw-card dw-alert" role="alert"><p class="dw-hint">{{ session('error') }}</p></section>
    @endif

    <div class="dw-panel">
        <div class="dw-panel-head"><div class="left"><span class="ic"><i class="fas fa-list-alt" aria-hidden="true"></i></span><h2>审批进度</h2></div></div>
        <div class="dw-panel-body">
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
        </div>
    </div>

    @if ($myTask)
        <div class="dw-panel">
            <div class="dw-panel-head"><div class="left"><span class="ic"><i class="fas fa-check-circle" aria-hidden="true"></i></span><h2>轮到我处理：{{ $myTask->step_name }}</h2></div></div>
            <div class="dw-panel-body">
            <form method="POST" action="{{ route('core.approvals.approve', ['approval' => $instance->id, 'task' => $myTask->id]) }}">
                @csrf
                <div class="dw-field">
                    <label for="comment">审批意见<span class="dw-help">（选填，最多 500 字）</span></label>
                    <textarea class="dw-input" id="comment" name="comment" rows="3" maxlength="500"
                              placeholder="例如：价格与合同一致，同意。"></textarea>
                </div>
                <div class="dw-page-actions">
                    <button type="submit" class="dw-btn dw-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> 同意</button>
                    <button type="submit" class="dw-btn dw-btn-danger"
                            formaction="{{ route('core.approvals.reject', ['approval' => $instance->id, 'task' => $myTask->id]) }}">
                        <i class="fas fa-times" aria-hidden="true"></i> 驳回
                    </button>
                </div>
            </form>
            </div>
        </div>
    @elseif ($instance->isPending())
        <div class="dw-panel"><div class="dw-panel-body"><p class="dw-hint mb-0">这条审批正在第 {{ $instance->current_step }} 步，你当前没有待处理的节点。</p></div></div>
    @endif

    @if (! empty($instance->payload))
        <div class="dw-panel">
            <div class="dw-panel-head"><div class="left"><span class="ic"><i class="fas fa-file-alt" aria-hidden="true"></i></span><h2>单据摘要</h2></div></div>
            <div class="dw-panel-body"><ul class="dw-plain-list">
                @foreach ($instance->payload as $key => $value)
                    <li><code>{{ $key }}</code><span>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</span></li>
                @endforeach
            </ul></div>
        </div>
    @endif
</div>
@endsection
