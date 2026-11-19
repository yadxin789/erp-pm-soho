@extends('soho::layouts.app')

@section('title', '退款单台账')

@section('content')
<div class="row g-3 mb-3">
    @foreach([
        ['退款单总数', $stats['total'], 'secondary'],
        ['待审核', $stats['pending'], 'warning'],
        ['退款中', $stats['processing'], 'info'],
        ['已退款', $stats['completed'], 'success'],
        ['失败/驳回', $stats['failed'], 'danger'],
    ] as [$label, $value, $color])
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-muted small mb-1">{{ $label }}</div>
                    <div class="fs-5 fw-semibold text-{{ $color }}">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-md-2 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-muted small mb-1">已退款金额</div>
                <div class="fs-5 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">退款单台账</h5>
        <a href="{{ route('soho.aftersale.refunds') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-list me-1"></i> 售后申请视图
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="退款单号 / 售后单号 / 系统单号 / 买家" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">全部状态</option>
                    @foreach(['pending' => '待审核', 'processing' => '退款中', 'completed' => '已退款', 'failed' => '失败/驳回'] as $k => $v)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">全部类型</option>
                    <option value="refund" @selected(request('type') === 'refund')>仅退款</option>
                    <option value="return_refund" @selected(request('type') === 'return_refund')>退货退款</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                <a href="{{ route('soho.refund.records') }}" class="btn btn-outline-secondary">重置</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>退款单号</th>
                        <th>售后单号</th>
                        <th>系统单号</th>
                        <th>买家</th>
                        <th class="text-end">退款金额</th>
                        <th class="text-center">类型</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">创建时间</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-monospace small">{{ $row->refund_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->after_sale_no ?: '-' }}</td>
                            <td class="font-monospace small text-muted">{{ $row->system_order_no ?: '-' }}</td>
                            <td>{{ $row->buyer_nick ?: '-' }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->refund_amount, 2) }}</td>
                            <td class="text-center small">{{ $row->refund_type === 'return_refund' ? '退货退款' : '仅退款' }}</td>
                            <td class="text-center">
                                @php
                                    $map = ['pending' => ['待审核', 'warning'], 'processing' => ['退款中', 'info'], 'completed' => ['已退款', 'success'], 'failed' => ['失败', 'danger']];
                                    [$label, $color] = $map[$row->refund_status] ?? [$row->refund_status, 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                            </td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                            <td class="text-center">
                                <a href="{{ route('soho.refund.show', $row->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-search me-1"></i> 详情
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                暂无退款单。可在下方由售后单生成退款单。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->total() > 0)
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="text-muted small">共 {{ $rows->total() }} 条</div>
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">由售后单生成退款单</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.refund.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6">
                <label class="form-label small text-muted">选择售后单</label>
                <select name="after_sale_id" class="form-select" required>
                    <option value="">请选择售后单</option>
                    @foreach($candidates as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->after_sale_no }}　{{ $c->buyer_nick }}　¥{{ number_format((float) $c->refund_amount, 2) }}　{{ $c->status }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">退款类型</label>
                <select name="refund_type" class="form-select">
                    <option value="">按售后类型自动</option>
                    <option value="refund">仅退款</option>
                    <option value="return_refund">退货退款</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="fa fa-plus me-1"></i> 生成退款单</button>
            </div>
        </form>
        @if($candidates->isEmpty())
            <div class="text-muted small mt-2">当前没有可生成退款单的售后单（已全部生成或状态不符）。</div>
        @endif
    </div>
</div>
@endsection
