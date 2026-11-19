@extends('soho::layouts.app')

@section('title', '采购报表')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">采购订单</div><div class="fs-4 fw-semibold">{{ $stats['order_count'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">采购金额</div><div class="fs-6 fw-semibold text-primary mt-2">¥{{ number_format($stats['order_amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待处理需求</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending_requests'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">采购计划预算</div><div class="fs-6 fw-semibold mt-2">¥{{ number_format($stats['plan_budget'], 2) }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">到货单</div><div class="fs-4 fw-semibold">{{ $stats['arrivals'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审批</div><div class="fs-4 fw-semibold text-danger">{{ $stats['approvals_pending'] }}</div>
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">按供应商汇总</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>供应商</th>
                                <th class="text-center">等级</th>
                                <th class="text-center">订单数</th>
                                <th class="text-end">采购金额</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bySupplier as $s)
                                <tr>
                                    <td>{{ $s->supplier_name ?: '供应商#'.$s->supplier_id }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $s->level ?: '—' }}</span></td>
                                    <td class="text-center">{{ $s->order_count }}</td>
                                    <td class="text-end">¥{{ number_format((float) $s->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">暂无供应商采购数据。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">订单状态分布</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead><tr><th class="text-center">状态</th><th class="text-center">订单数</th><th class="text-end">金额</th></tr></thead>
                        <tbody>
                            @forelse($byStatus as $s)
                                <tr>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $s->status }}</span></td>
                                    <td class="text-center">{{ $s->cnt }}</td>
                                    <td class="text-end">¥{{ number_format((float) $s->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">暂无订单。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">需求单状态</h5></div>
            <div class="card-body">
                @forelse($requestStatus as $r)
                    <span class="badge bg-light text-dark border me-2 mb-2">{{ $r->status }} <span class="text-primary">{{ $r->cnt }}</span></span>
                @empty
                    <span class="text-muted small">暂无采购需求单。</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">月度采购趋势</h5>
        <a href="{{ route('soho.price.change.history') }}" class="btn btn-outline-secondary btn-sm">调价历史</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead><tr><th class="text-center">月份</th><th class="text-center">订单数</th><th class="text-end">采购金额</th></tr></thead>
                <tbody>
                    @forelse($monthly as $m)
                        <tr>
                            <td class="text-center font-monospace">{{ $m->ym }}</td>
                            <td class="text-center">{{ $m->cnt }}</td>
                            <td class="text-end">¥{{ number_format((float) $m->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">暂无月度数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
