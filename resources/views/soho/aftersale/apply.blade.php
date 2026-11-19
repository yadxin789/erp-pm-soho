@extends('soho::layouts.app')

@section('title', '售后申请')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">售后单总数</div>
            <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">待处理</div>
            <div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">申请金额合计</div>
            <div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">登记售后申请</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.aftersale.apply.store') }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">关联订单（已发货 / 已完成）</label>
                    <select name="order_id" class="form-select" required>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->system_order_no }} · {{ $order->buyer_nick }} · ¥{{ number_format((float) $order->pay_amount, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">售后类型</label>
                    <select name="type" class="form-select" required>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">退款金额</label>
                    <input type="number" step="0.01" min="0" name="refund_amount" class="form-control" value="0" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">售后原因</label>
                    <input type="text" name="reason" class="form-control" placeholder="如：尺寸不合适" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-plus me-1"></i> 登记</button>
                </div>
            </div>
            <div class="mt-2">
                <input type="text" name="description" class="form-control" placeholder="补充说明（选填）">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">售后单列表</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('soho.aftersale.supplement') }}" class="btn btn-outline-secondary btn-sm">待补充资料</a>
            <a href="{{ route('soho.aftersale.progress') }}" class="btn btn-outline-secondary btn-sm">售后进度</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索售后单号 / 买家 / 订单号 / 原因" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.aftersale.apply') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>售后单号</th>
                        <th>关联订单</th>
                        <th>买家</th>
                        <th class="text-center">类型</th>
                        <th class="text-center">状态</th>
                        <th class="text-end">退款金额</th>
                        <th>原因</th>
                        <th class="text-center">申请时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-monospace small">{{ $row->after_sale_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->system_order_no ?: '-' }}</td>
                            <td>{{ $row->buyer_nick ?: '-' }}</td>
                            <td class="text-center small">
                                @php $typeMap = ['refund' => '仅退款', 'return' => '退货退款', 'exchange' => '换货']; @endphp
                                {{ $typeMap[$row->type] ?? $row->type }}
                            </td>
                            <td class="text-center">
                                @php $st = ['pending' => ['待处理', 'warning'], 'approved' => ['已同意', 'success'], 'rejected' => ['已拒绝', 'danger'], 'completed' => ['已完成', 'secondary']]; @endphp
                                <span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span>
                            </td>
                            <td class="text-end">¥{{ number_format((float) $row->refund_amount, 2) }}</td>
                            <td class="small">{{ $row->reason ?: '-' }}</td>
                            <td class="text-center small text-muted">{{ $row->apply_time }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">还没有售后单，可在上方表单登记一条。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
