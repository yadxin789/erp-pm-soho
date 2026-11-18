@extends('soho::layouts.app')
@section('title', '利润报表')
@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">筛选条件</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.report.profit') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">开始日期</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-d', strtotime('-30 days'))) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">结束日期</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">店铺</label>
                <select name="shop_id" class="form-select">
                    <option value="">全部店铺</option>
                    @foreach($shops ?? [] as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2"><i class="fa fa-search"></i> 查询</button>
                <a href="{{ route('soho.report.profit') }}" class="btn btn-secondary">重置</a>
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
            <div class="col-md-3">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-info mb-0">¥{{ number_format($summary['total_revenue'] ?? 0, 2) }}</h3>
                        <p class="text-muted mb-0 mt-2">总收入</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-warning mb-0">¥{{ number_format($summary['total_cost'] ?? 0, 2) }}</h3>
                        <p class="text-muted mb-0 mt-2">总成本</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-success mb-0">¥{{ number_format($summary['total_profit'] ?? 0, 2) }}</h3>
                        <p class="text-muted mb-0 mt-2">总利润</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="{{ ($summary['profit_margin'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0">{{ number_format($summary['profit_margin'] ?? 0, 2) }}%</h3>
                        <p class="text-muted mb-0 mt-2">利润率</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">利润趋势</h5>
    </div>
    <div class="card-body">
        <div id="profit-chart" style="width: 100%; height: 350px;"></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">利润明细</h5>
        <a href="{{ url('report/profit/export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-success"><i class="fa fa-download"></i> 导出</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th class="text-end">收入</th>
                        <th class="text-end">成本</th>
                        <th class="text-end">利润</th>
                        <th class="text-end">利润率</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profits as $profit)
                        <tr>
                            <td>{{ $profit->date ?? $profit->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end">¥{{ number_format($profit->revenue ?? 0, 2) }}</td>
                            <td class="text-end">¥{{ number_format($profit->cost ?? 0, 2) }}</td>
                            <td class="text-end {{ ($profit->profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">¥{{ number_format($profit->profit ?? 0, 2) }}</td>
                            <td class="text-end {{ ($profit->profit_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($profit->profit_margin ?? 0, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">暂无数据</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var chartDom = document.getElementById('profit-chart');
        if (!chartDom || typeof echarts === 'undefined') return;

        var chart = echarts.init(chartDom);
        var dates = {!! json_encode(collect($profits)->pluck('date')->map(fn($d) => $d ?? (is_object($d) ? $d->format('Y-m-d') : $d))->values()) !!};
        var revenues = {!! json_encode(collect($profits)->pluck('revenue')->map(fn($v) => round($v ?? 0, 2))->values()) !!};
        var costs = {!! json_encode(collect($profits)->pluck('cost')->map(fn($v) => round($v ?? 0, 2))->values()) !!};
        var profits = {!! json_encode(collect($profits)->pluck('profit')->map(fn($v) => round($v ?? 0, 2))->values()) !!};

        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'cross' }
            },
            legend: {
                data: ['收入', '成本', '利润']
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
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: '¥{value}'
                }
            },
            series: [
                {
                    name: '收入',
                    type: 'line',
                    data: revenues,
                    smooth: true,
                    areaStyle: { opacity: 0.3 },
                    itemStyle: { color: '#17a2b8' }
                },
                {
                    name: '成本',
                    type: 'line',
                    data: costs,
                    smooth: true,
                    areaStyle: { opacity: 0.3 },
                    itemStyle: { color: '#ffc107' }
                },
                {
                    name: '利润',
                    type: 'line',
                    data: profits,
                    smooth: true,
                    areaStyle: { opacity: 0.3 },
                    itemStyle: { color: '#28a745' }
                }
            ]
        };

        chart.setOption(option);
        window.addEventListener('resize', function() { chart.resize(); });
    });
</script>
@endsection