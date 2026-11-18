@extends('soho::layouts.app')
@section('title', '销售报表')
@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">筛选条件</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.report.sales') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">开始日期</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-d', strtotime('-30 days'))) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">结束日期</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">店铺</label>
                <select name="shop_id" class="form-select">
                    <option value="">全部店铺</option>
                    @foreach($shops ?? [] as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">平台</label>
                <select name="platform" class="form-select">
                    <option value="">全部平台</option>
                    <option value="taobao" {{ request('platform') == 'taobao' ? 'selected' : '' }}>淘宝</option>
                    <option value="jd" {{ request('platform') == 'jd' ? 'selected' : '' }}>京东</option>
                    <option value="pdd" {{ request('platform') == 'pdd' ? 'selected' : '' }}>拼多多</option>
                    <option value="douyin" {{ request('platform') == 'douyin' ? 'selected' : '' }}>抖音</option>
                    <option value="amazon" {{ request('platform') == 'amazon' ? 'selected' : '' }}>亚马逊</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary me-2"><i class="fa fa-search"></i> 查询</button>
                <a href="{{ route('soho.report.sales') }}" class="btn btn-secondary">重置</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">统计概览</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-primary mb-0">{{ number_format($summary['total_orders'] ?? 0) }}</h3>
                        <p class="text-muted mb-0 mt-2">总订单数</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-info mb-0">¥{{ number_format($summary['total_sales'] ?? 0, 2) }}</h3>
                        <p class="text-muted mb-0 mt-2">总销售额</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-success mb-0">¥{{ number_format($summary['avg_order_value'] ?? 0, 2) }}</h3>
                        <p class="text-muted mb-0 mt-2">客单价</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">销售趋势</h5>
    </div>
    <div class="card-body">
        <div id="sales-chart" style="width: 100%; height: 350px;"></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">销售明细</h5>
        <a href="{{ route('soho.report.sales.export', request()->query()) }}" class="btn btn-success"><i class="fa fa-download"></i> 导出</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th class="text-end">订单数</th>
                        <th class="text-end">销售额</th>
                        <th class="text-end">退款额</th>
                        <th class="text-end">净销售额</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->date ?? $sale->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end">{{ number_format($sale->order_count ?? 0) }}</td>
                            <td class="text-end">¥{{ number_format($sale->sales_amount ?? 0, 2) }}</td>
                            <td class="text-end text-danger">¥{{ number_format($sale->refund_amount ?? 0, 2) }}</td>
                            <td class="text-end text-success">¥{{ number_format(($sale->sales_amount ?? 0) - ($sale->refund_amount ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">暂无数据</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($sales, 'hasPages') && $sales->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartDom = document.getElementById('sales-chart');
        if (!chartDom || typeof echarts === 'undefined') return;

        var chart = echarts.init(chartDom);
        var dates = {!! json_encode(collect($sales)->pluck('date')->map(fn($d) => $d ?? (is_object($d) ? $d->format('Y-m-d') : $d))->values()) !!};
        var orderCounts = {!! json_encode(collect($sales)->pluck('order_count')->map(fn($v) => $v ?? 0)->values()) !!};
        var salesAmounts = {!! json_encode(collect($sales)->pluck('sales_amount')->map(fn($v) => round($v ?? 0, 2))->values()) !!};
        var refundAmounts = {!! json_encode(collect($sales)->pluck('refund_amount')->map(fn($v) => round($v ?? 0, 2))->values()) !!};

        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' }
            },
            legend: {
                data: ['订单数', '销售额', '退款额']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: dates,
                boundaryGap: false
            },
            yAxis: [
                {
                    type: 'value',
                    name: '订单数',
                    position: 'left'
                },
                {
                    type: 'value',
                    name: '金额(¥)',
                    position: 'right',
                    axisLabel: {
                        formatter: '¥{value}'
                    }
                }
            ],
            series: [
                {
                    name: '订单数',
                    type: 'bar',
                    data: orderCounts,
                    itemStyle: { color: '#667eea' }
                },
                {
                    name: '销售额',
                    type: 'line',
                    yAxisIndex: 1,
                    data: salesAmounts,
                    smooth: true,
                    itemStyle: { color: '#28a745' }
                },
                {
                    name: '退款额',
                    type: 'line',
                    yAxisIndex: 1,
                    data: refundAmounts,
                    smooth: true,
                    itemStyle: { color: '#dc3545' }
                }
            ]
        };

        chart.setOption(option);
        window.addEventListener('resize', function() { chart.resize(); });
    });
</script>
@endsection