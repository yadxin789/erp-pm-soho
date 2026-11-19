@extends('soho::layouts.app')

@section('title', '促销分析')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">活动总数</div><div class="fs-4 fw-semibold">{{ $stats['promotions'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">生效中</div><div class="fs-4 fw-semibold text-success">{{ $stats['active'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">优惠券核销次数</div><div class="fs-4 fw-semibold">{{ $stats['coupon_usages'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">累计优惠金额</div><div class="fs-4 fw-semibold text-danger">¥{{ number_format($stats['discount_total'], 2) }}</div>
    </div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">按活动类型</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead><tr><th>类型</th><th class="text-center">活动数</th><th class="text-center">生效中</th></tr></thead>
                        <tbody>
                            @forelse($byType as $t)
                                <tr>
                                    <td>{{ ['fixed_discount' => '满减', 'percentage' => '折扣', 'buy_get' => '买赠'][$t->type] ?? $t->type }}</td>
                                    <td class="text-center">{{ $t->cnt }}</td>
                                    <td class="text-center text-success">{{ $t->active_cnt }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">暂无数据。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">活动状态 / 平台同步状态</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    @foreach($byStatus as $s)
                        <span class="badge bg-light text-dark border me-2 mb-2">{{ $s->status }} <span class="text-primary">{{ $s->cnt }}</span></span>
                    @endforeach
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead><tr><th class="text-center">平台同步状态</th><th class="text-center">数量</th></tr></thead>
                        <tbody>
                            @forelse($mapping as $m)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-{{ ['synced' => 'success', 'syncing' => 'info', 'pending' => 'warning', 'failed' => 'danger'][$m->sync_status] ?? 'secondary' }}">{{ $m->sync_status }}</span>
                                    </td>
                                    <td class="text-center">{{ $m->cnt }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">暂无平台活动映射。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">优惠券效果</h5>
        <a href="{{ route('soho.promotion.internal') }}" class="btn btn-outline-secondary btn-sm">内部促销</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>优惠券</th>
                        <th class="text-center">类型</th>
                        <th class="text-end">面值</th>
                        <th class="text-end">门槛</th>
                        <th class="text-center">发放 / 已用（账面）</th>
                        <th class="text-center">核销记录</th>
                        <th class="text-end">累计优惠</th>
                        <th class="text-center">核销率</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $c)
                        @php
                            $issued = (int) $c->total_quantity;
                            $rate = $issued > 0 ? round((int) $c->usage_count / $issued * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="font-monospace">{{ $c->code }}</div>
                                <div class="small text-muted">{{ ['active' => '生效中', 'inactive' => '已停用', 'expired' => '已过期'][$c->status] ?? $c->status }}</div>
                            </td>
                            <td class="text-center">{{ ['fixed_discount' => '满减', 'percentage' => '折扣'][$c->type] ?? $c->type }}</td>
                            <td class="text-end">¥{{ number_format((float) $c->value, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $c->min_amount, 2) }}</td>
                            <td class="text-center">{{ $issued }} / {{ $c->used_quantity }}</td>
                            <td class="text-center">{{ $c->usage_count }} <span class="small text-muted">(已用 {{ $c->used_count }})</span></td>
                            <td class="text-end text-danger">¥{{ number_format((float) $c->discount_sum, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'secondary') }}">{{ $rate }}%</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">暂无优惠券数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
