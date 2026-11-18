@extends('soho::layouts.app')
@section('title', '订单时效报表')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">订单时效报表</h5>
        <a href="{{ route('soho.report.order-timeliness.export') }}" class="btn btn-success"><i class="fa fa-download"></i> 导出</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ $stats['total_orders'] }}</h5><p class="mb-0 text-muted">总订单</p></div></div></div>
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ $stats['shipped_orders'] }}</h5><p class="mb-0 text-muted">已发货</p></div></div></div>
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ round($stats['avg_timeliness'], 1) }}h</h5><p class="mb-0 text-muted">平均时效</p></div></div></div>
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-success">{{ $stats['on_time_rate'] }}%</h5><p class="mb-0 text-muted">24小时准时率</p></div></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>订单号</th><th>店铺</th><th>平台</th><th>金额</th><th>下单时间</th><th>发货时间</th><th>时效(小时)</th></tr></thead>
                <tbody>
                    @forelse($orders as $o)
                    <tr>
                        <td>{{ $o->order_number }}</td>
                        <td>{{ $o->shop->name ?? '-' }}</td>
                        <td>{{ $o->platform }}</td>
                        <td>¥{{ $o->total_amount }}</td>
                        <td>{{ $o->order_created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $o->shipped_at ?? '-' }}</td>
                        <td>{{ $o->ship_time && $o->order_created_at ? round(\Carbon\Carbon::parse($o->order_created_at)->diffInHours($o->ship_time), 1) : '-' }}</td>
                    </tr>
                    @empty<tr><td colspan="7" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection