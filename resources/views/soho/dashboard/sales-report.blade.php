@extends('soho::layouts.app')

@section('title', '销售报表')

@section('content')
<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">订单总数</h6>
                <h3 class="text-primary">{{ number_format($salesStats['total_orders']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">销售总额</h6>
                <h3 class="text-success">¥{{ number_format($salesStats['total_sales'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">平均订单金额</h6>
                <h3 class="text-warning">¥{{ number_format($salesStats['avg_order_amount'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- 筛选表单 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('soho.sales.report') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">开始日期</label>
                <input type="date" name="date_start" class="form-control" value="{{ $dateStart }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">结束日期</label>
                <input type="date" name="date_end" class="form-control" value="{{ $dateEnd }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">店铺</label>
                <select name="shop_id" class="form-select">
                    <option value="">全部店铺</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">平台</label>
                <select name="platform" class="form-select">
                    <option value="">全部平台</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ strtoupper($platform) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search me-1"></i>查询
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 图表区域 -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">销售趋势</h5>
            </div>
            <div class="card-body">
                <div id="salesTrendChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">平台销售占比</h5>
            </div>
            <div class="card-body">
                <div id="platformChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- 订单列表 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">订单明细</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>订单号</th>
                        <th>店铺</th>
                        <th>平台</th>
                        <th>买家</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th>下单时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->shop->name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $order->platform }}</span></td>
                        <td>{{ $order->buyer_nickname ?? '-' }}</td>
                        <td>¥{{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">暂无订单数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/soho/js/echarts.min.js') }}"></script>
<script>
    // 销售趋势图
    var salesTrendChart = echarts.init(document.getElementById('salesTrendChart'));
    var dailyData = @json($dailySales);
    salesTrendChart.setOption({
        tooltip: { trigger: 'axis' },
        legend: { data: ['订单数', '销售额'] },
        xAxis: { type: 'category', data: dailyData.map(d => d.date) },
        yAxis: [
            { type: 'value', name: '订单数' },
            { type: 'value', name: '销售额' }
        ],
        series: [
            { name: '订单数', type: 'line', data: dailyData.map(d => d.count), itemStyle: { color: '#667eea' } },
            { name: '销售额', type: 'bar', yAxisIndex: 1, data: dailyData.map(d => d.amount), itemStyle: { color: '#28a745' } }
        ]
    });

    // 平台销售占比
    var platformChart = echarts.init(document.getElementById('platformChart'));
    var platformData = @json($platformSales);
    platformChart.setOption({
        tooltip: { trigger: 'item', formatter: '{b}: ¥{c} ({d}%)' },
        series: [{
            type: 'pie',
            radius: ['40%', '70%'],
            data: platformData.map(p => ({ value: p.total, name: p.platform }))
        }]
    });

    window.addEventListener('resize', function() {
        salesTrendChart.resize();
        platformChart.resize();
    });
</script>
@endsection
