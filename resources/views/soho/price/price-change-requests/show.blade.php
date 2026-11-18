@extends('soho::layouts.app')

@section('title', '调价申请详情')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.price.change.requests.index') }}">调价申请</a></li>
        <li class="breadcrumb-item active">申请详情</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-file-text me-2"></i>调价申请详情</h4>
            <div>
                @if($request->status === 'pending')
                    <a href="{{ route('soho.price.change.requests.edit', $request) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-edit me-1"></i>编辑
                    </a>
                @endif
                <a href="{{ route('soho.price.change.requests.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i>返回列表
                </a>
            </div>
        </div>

        {{-- 申请状态卡片 --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'cancelled' => 'secondary',
                            'executed' => 'info',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }} fs-6">
                        {{ $statusLabels[$request->status] ?? $request->status }}
                    </span>
                </div>
                <span class="small text-muted">申请编号: #{{ $request->id }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="120">商品名称</th><td>{{ $request->product->name ?? '-' }}</td></tr>
                            <tr><th>商品编码</th><td><code>{{ $request->product->product_code ?? '-' }}</code></td></tr>
                            <tr><th>SKU</th><td>{{ $request->sku ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="120">当前价格</th><td class="text-muted">¥{{ number_format($request->current_price ?? 0, 2) }}</td></tr>
                            <tr><th>申请价格</th><td class="fw-bold text-primary" style="font-size: 1.2rem;">¥{{ number_format($request->requested_price ?? 0, 2) }}</td></tr>
                            <tr>
                                <th>变动幅度</th>
                                <td>
                                    @php
                                        $changePercent = $request->current_price > 0 ? (($request->requested_price - $request->current_price) / $request->current_price) * 100 : 0;
                                    @endphp
                                    @if($changePercent > 0)
                                        <span class="badge bg-success fs-6">↑ +{{ number_format($changePercent, 2) }}%</span>
                                    @elseif($changePercent < 0)
                                        <span class="badge bg-danger fs-6">↓ {{ number_format($changePercent, 2) }}%</span>
                                    @else
                                        <span class="badge bg-secondary">0%</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <table class="table table-borderless mb-0">
                    <tr><th width="120">调价原因</th><td>{{ $request->reason ?? '-' }}</td></tr>
                    <tr><th>备注</th><td>{{ $request->remark ?? '-' }}</td></tr>
                    <tr><th>申请人</th><td>{{ $request->applicant->name ?? '-' }}</td></tr>
                    <tr><th>申请时间</th><td>{{ $request->created_at?->format('Y-m-d H:i:s') }}</td></tr>
                </table>
            </div>
        </div>

        {{-- 状态时间线 --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-clock-o me-2"></i>状态时间线</h6></div>
            <div class="card-body">
                <div class="timeline">
                    {{-- 申请 --}}
                    <div class="d-flex mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; flex-shrink: 0;">
                            <i class="fa fa-file-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">提交申请</div>
                            <div class="small text-muted">{{ $request->applicant->name ?? '-' }} · {{ $request->created_at?->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>

                    {{-- 审批 --}}
                    <div class="d-flex mb-4">
                        <div class="rounded-circle {{ $request->approved_at ? ($request->status === 'approved' ? 'bg-success' : 'bg-danger') : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; flex-shrink: 0;">
                            <i class="fa fa-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">
                                {{ $request->approved_at ? ($request->status === 'approved' ? '审批通过' : '审批驳回') : '等待审批' }}
                            </div>
                            @if($request->approved_at)
                                <div class="small text-muted">{{ $request->approver->name ?? '-' }} · {{ $request->approved_at->format('Y-m-d H:i:s') }}</div>
                                @if($request->approval_remark)
                                    <div class="small text-muted mt-1">备注：{{ $request->approval_remark }}</div>
                                @endif
                            @else
                                <div class="small text-muted text-primary">审批人：{{ $request->approver->name ?? '待分配' }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- 执行 --}}
                    <div class="d-flex">
                        <div class="rounded-circle {{ $request->executed_at ? 'bg-info' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; flex-shrink: 0;">
                            <i class="fa fa-cog"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">
                                {{ $request->executed_at ? '价格已执行' : ($request->status === 'approved' ? '等待执行' : '未执行') }}
                            </div>
                            @if($request->executed_at)
                                <div class="small text-muted">执行人：{{ $request->executor->name ?? '-' }} · {{ $request->executed_at->format('Y-m-d H:i:s') }}</div>
                            @else
                                <div class="small text-muted">自动或手动同步至各平台</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 操作区 --}}
        @if($request->status === 'pending')
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.price.change.requests.cancel', $request) }}" class="d-inline" onsubmit="return confirm('确定取消此申请？')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa fa-times me-1"></i>取消申请
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection