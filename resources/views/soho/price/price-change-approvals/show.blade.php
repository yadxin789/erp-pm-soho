@extends('soho::layouts.app')

@section('title', '审批详情')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('price.price-change-approvals.index') }}">调价审批</a></li>
        <li class="breadcrumb-item active">审批详情</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-gavel me-2"></i>审批详情</h4>
            <a href="{{ route('price.price-change-approvals.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        {{-- 申请信息 --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-file-text me-2"></i>申请信息</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="120">申请编号</th><td>#{{ $approval->id }}</td></tr>
                            <tr><th>商品名称</th><td>{{ $approval->product->name ?? '-' }}</td></tr>
                            <tr><th>商品编码</th><td><code>{{ $approval->product->product_code ?? '-' }}</code></td></tr>
                            <tr><th>SKU</th><td>{{ $approval->sku ?? '-' }}</td></tr>
                            <tr><th>申请人</th><td>{{ $approval->applicant->name ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="120">当前价格</th><td class="text-muted">¥{{ number_format($approval->current_price ?? 0, 2) }}</td></tr>
                            <th>申请价格</th><td class="fw-bold text-primary" style="font-size: 1.2rem;">¥{{ number_format($approval->requested_price ?? 0, 2) }}</td></tr>
                            <tr>
                                <th>变动幅度</th>
                                <td>
                                    @php
                                        $changePercent = $approval->current_price > 0 ? (($approval->requested_price - $approval->current_price) / $approval->current_price) * 100 : 0;
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
                            <tr><th>申请时间</th><td>{{ $approval->created_at?->format('Y-m-d H:i:s') }}</td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <table class="table table-borderless mb-0">
                    <tr><th width="120">调价原因</th><td>{{ $approval->reason ?? '-' }}</td></tr>
                    <tr><th>备注</th><td>{{ $approval->remark ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- 审批操作 --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-check-square me-2"></i>审批操作</h6></div>
            <div class="card-body">
                @if($approval->status === 'pending')
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle me-2"></i>
                        请仔细审核以上信息，确认无误后通过审批。如信息有误可驳回申请。
                    </div>

                    <form method="POST" action="{{ route('price.price-change-approvals.approve', $approval) }}" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">审批备注（可选）</label>
                            <textarea name="approval_remark" class="form-control" rows="2" placeholder="如：价格合理，予以通过"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check me-1"></i>批准通过
                        </button>
                    </form>

                    <hr>

                    <form method="POST" action="{{ route('price.price-change-approvals.reject', $approval) }}" onsubmit="return confirm('确认驳回此申请？驳回后需要申请人重新提交。')">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">驳回原因 <span class="text-danger">*</span></label>
                            <textarea name="approval_remark" class="form-control" rows="2" required placeholder="请说明驳回原因"></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fa fa-times me-1"></i>驳回申请
                        </button>
                    </form>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fa fa-check-circle fa-2x mb-2 text-success"></i>
                        <div>此申请已完成审批</div>
                        <div class="mt-2">
                            @php
                                $statusColors = [
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$approval->status] ?? 'secondary' }} fs-6">
                                {{ $statusLabels[$approval->status] ?? $approval->status }}
                            </span>
                        </div>
                        @if($approval->approval_remark)
                            <div class="mt-2 small">审批备注：{{ $approval->approval_remark }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection