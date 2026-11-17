@extends('core::layouts.base')

@section('title', '发起审批 · '.config('app.name'))

@section('content')
<div class="dw-page">
    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-plus" aria-hidden="true"></i></span>
                <h2>发起审批 <span class="sub">{{ strtoupper($module) }} · 按单据类型匹配审批流</span></h2>
            </div>
            <div class="tools">
                <a class="dw-btn" href="{{ route('core.approvals.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> 返回审批中心</a>
            </div>
        </div>
        <div class="dw-panel-body">

    @if ($errors->any())
        <section class="dw-card dw-alert" role="alert">
            @foreach ($errors->all() as $error)
                <p class="dw-hint">{{ $error }}</p>
            @endforeach
        </section>
    @endif
    @if (session('error'))
        <section class="dw-card dw-alert" role="alert"><p class="dw-hint">{{ session('error') }}</p></section>
    @endif

    @if ($flows->isEmpty())
            <div class="dw-empty-panel">
                <i class="far fa-file-alt" aria-hidden="true"></i>
                <p class="t">当前模块还没有启用的审批流</p>
                <p class="mb-0">等模块迁入后，审批流会随业务一起导入；也可以让管理员在后台新增。</p>
            </div>
    @else
        <div>
            <form method="POST" action="{{ route('core.approvals.store') }}">
                @csrf
                <div class="dw-field">
                    <label for="approval_flow_id">审批流<span class="req">*</span></label>
                    <select class="dw-input" id="approval_flow_id" name="approval_flow_id" required>
                        @foreach ($flows as $flow)
                            <option value="{{ $flow->id }}" @selected(old('approval_flow_id') == $flow->id)>
                                {{ $flow->name }}（{{ $flow->document_type }} · {{ $flow->steps->map(fn ($s) => $s->step_order.'.'.$s->name)->implode(' → ') }}）
                            </option>
                        @endforeach
                    </select>
                    <span class="dw-help">审批流由单据类型决定；括号里是这条流的步骤顺序，同一步任一人同意即前进。</span>
                </div>

                <div class="dw-field">
                    <label for="document_id">单据号<span class="req">*</span></label>
                    <input class="dw-input" id="document_id" name="document_id" type="number" min="1"
                           value="{{ old('document_id') }}" required>
                    <span class="dw-help">业务单据的 id（当前模块业务迁入后由业务侧自动带入，这里用于演示与手工补录）。</span>
                </div>

                <div class="dw-field">
                    <label for="title">标题<span class="req">*</span></label>
                    <input class="dw-input" id="title" name="title" type="text" maxlength="120"
                           value="{{ old('title') }}" required placeholder="例如：采购订单 PO-2026-0007 付款审批">
                </div>

                <div class="dw-field">
                    <label for="note">说明</label>
                    <textarea class="dw-input" id="note" name="note" rows="3" maxlength="500"
                              placeholder="给审批人看的补充说明（选填）。">{{ old('note') }}</textarea>
                </div>

                <div class="dw-page-actions">
                    <button type="submit" class="dw-btn">提交审批</button>
                </div>
            </form>
        </div>
    @endif
        </div>
    </div>
</div>
@endsection
