@extends('soho::layouts.app')

@section('title', '退款流水')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">流水笔数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">退款金额合计</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待处理</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已退款</div><div class="fs-4 fw-semibold text-success">{{ $stats['refunded'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">退款流水</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待处理', 'approved' => '已同意', 'refunded' => '已退款', 'rejected' => '已拒绝'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="退款单号 / 订单号 / 客户" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.refund.flow') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>退款单号</th>
                        <th>订单号</th>
                        <th>客户</th>
                        <th class="text-end">退款金额</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['pending' => ['待处理', 'warning'], 'approved' => ['已同意', 'info'], 'refunded' => ['已退款', 'success'], 'rejected' => ['已拒绝', 'danger']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->refund_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->order_no }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无退款流水。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">最近退款单执行情况</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>退款单号</th><th class="text-center">类型</th><th class="text-end">金额</th><th class="text-center">状态</th><th>失败原因</th><th class="text-center">创建</th></tr></thead>
                <tbody>
                    @forelse($records as $r)
                        @php $rs = ['pending' => ['待执行', 'warning'], 'processing' => ['执行中', 'info'], 'completed' => ['已完成', 'success'], 'failed' => ['失败', 'danger']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $r->refund_no }}</td>
                            <td class="text-center small">{{ $r->refund_type }}</td>
                            <td class="text-end">¥{{ number_format((float) $r->refund_amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $rs[$r->refund_status][1] ?? 'secondary' }}">{{ $rs[$r->refund_status][0] ?? $r->refund_status }}</span></td>
                            <td class="small text-danger">{{ $r->failed_reason ?: '-' }}</td>
                            <td class="text-center small text-muted">{{ $r->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无退款单。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
