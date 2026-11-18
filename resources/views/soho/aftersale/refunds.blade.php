@extends('soho::layouts.app')
@section('title', '退款记录')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">退款记录</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') == 'pending')>待退款</option>
                        <option value="processing" @selected(request('status') == 'processing')>退款中</option>
                        <option value="completed" @selected(request('status') == 'completed')>已退款</option>
                        <option value="failed" @selected(request('status') == 'failed')>退款失败</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                </div>
                <div class="col-md-5">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.aftersale.refunds') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>售后单号</th>
                        <th>订单号</th>
                        <th>店铺</th>
                        <th>退款金额</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th>处理时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $refund)
                    <tr>
                        <td>{{ $refund->after_sale_no }}</td>
                        <td>{{ $refund->platformOrder->platform_order_no ?? '-' }}</td>
                        <td>{{ $refund->shop->name ?? '-' }}</td>
                        <td>¥{{ number_format($refund->refund_amount, 2) }}</td>
                        <td>
                            @if($refund->status == 'pending')<span class="badge bg-warning">待退款</span>
                            @elseif($refund->status == 'processing')<span class="badge bg-info">退款中</span>
                            @elseif($refund->status == 'completed')<span class="badge bg-success">已退款</span>
                            @elseif($refund->status == 'failed')<span class="badge bg-danger">退款失败</span>
                            @else<span class="badge bg-secondary">{{ $refund->status }}</span>@endif
                        </td>
                        <td>{{ $refund->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $refund->handle_time?->format('Y-m-d H:i') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">暂无退款记录</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $refunds->links() }}
    </div>
</div>
@endsection