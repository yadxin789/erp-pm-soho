@extends('soho::layouts.app')
@php $approvals = $approvals ?? collect(); @endphp

@section('title', '调价审批')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">价格中心</a></li>
        <li class="breadcrumb-item active">调价审批</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-gavel me-2"></i>调价审批</h4>
    <div>
        @if(isset($pendingCount) && $pendingCount > 0)
            <span class="badge bg-warning fs-6">{{ $pendingCount }} 待审批</span>
        @endif
    </div>
</div>

{{-- 待审批列表 --}}
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-clock-o me-2"></i>待审批列表</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>商品</th>
                        <th>申请人</th>
                        <th>当前价格</th>
                        <th>申请价格</th>
                        <th>变动%</th>
                        <th>原因</th>
                        <th>申请时间</th>
                        <th width="180">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvals ?? collect() as $index => $approval)
                        @php
                            $changePercent = $approval->current_price > 0 ? (($approval->requested_price - $approval->current_price) / $approval->current_price) * 100 : 0;
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $approval->product->name ?? '-' }}</div>
                                <code class="small text-muted">{{ $approval->sku ?? '' }}</code>
                            </td>
                            <td>{{ $approval->applicant->name ?? '-' }}</td>
                            <td class="text-muted">¥{{ number_format($approval->current_price ?? 0, 2) }}</td>
                            <td class="fw-semibold">¥{{ number_format($approval->requested_price ?? 0, 2) }}</td>
                            <td>
                                @if($changePercent > 0)
                                    <span class="badge bg-success">+{{ number_format($changePercent, 1) }}%</span>
                                @elseif($changePercent < 0)
                                    <span class="badge bg-danger">{{ number_format($changePercent, 1) }}%</span>
                                @else
                                    <span class="badge bg-secondary">0%</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ Str::limit($approval->reason ?? '-', 30) }}</td>
                            <td class="small">{{ $approval->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('soho.price.change.approvals.show', $approval) }}" class="btn btn-sm btn-outline-primary" title="查看详情">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('soho.price.change.approvals.approve', $approval) }}" class="d-inline" onsubmit="return confirm('确认批准此调价申请？')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="批准">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('soho.price.change.approvals.reject', $approval) }}" class="d-inline" onsubmit="return confirm('确认驳回此调价申请？')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="驳回">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa fa-check-circle fa-2x mb-2"></i>
                                <div>暂无待审批的申请</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($approvals, 'links'))
        <div class="card-footer">
            {{ $approvals->links() }}
        </div>
    @endif
</div>
@endsection