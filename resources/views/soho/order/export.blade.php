@extends('soho::layouts.app')

@section('title', '订单导出')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">订单导出</h5>
        <a href="{{ route('soho.order.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回订单
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.order.export') }}" class="mb-4">
            <input type="hidden" name="download" value="1">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">订单状态</label>
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">下单日期（起）</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">下单日期（止）</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-download me-1"></i> 导出 CSV
                    </button>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">
                当前条件共 <strong>{{ $total }}</strong> 条订单，下面显示最近 10 条预览（导出文件含全部命中记录，UTF-8 BOM 编码，Excel 可直接打开）。
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>系统单号</th>
                        <th>平台单号</th>
                        <th>平台</th>
                        <th>买家</th>
                        <th class="text-center">订单状态</th>
                        <th class="text-center">支付状态</th>
                        <th class="text-end">订单金额</th>
                        <th class="text-end">实付金额</th>
                        <th class="text-center">下单时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preview as $order)
                        <tr>
                            <td class="font-monospace small">{{ $order->system_order_no }}</td>
                            <td class="font-monospace small text-muted">{{ $order->platform_order_no }}</td>
                            <td>{{ $order->platform ?: '-' }}</td>
                            <td>{{ $order->buyer_nick ?: '-' }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $statuses[$order->order_status] ?? $order->order_status }}</span></td>
                            <td class="text-center small">{{ $order->pay_status ?: '-' }}</td>
                            <td class="text-end">¥{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $order->pay_amount, 2) }}</td>
                            <td class="text-center small text-muted">{{ $order->order_created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                当前筛选条件没有订单，换个条件再导出。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
