@extends('soho::layouts.app')

@section('title', '仪表盘')

@section('content')
<div class="row">
    <!-- 统计卡片 -->
    <div class="col-md-3">
        <div class="card stat-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">店铺总数</h6>
                        <h3 class="mb-0">{{ $stats['total_shops'] }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fa fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">订单总数</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_orders']) }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fa fa-shopping-bag fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">商品总数</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_products']) }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fa fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">用户总数</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                    </div>
                    <div class="text-info">
                        <i class="fa fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 今日/昨日统计 -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-calendar me-2"></i>今日数据</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4>{{ $stats['today_orders'] }}</h4>
                        <p class="text-muted mb-0">今日订单</p>
                    </div>
                    <div class="col-6">
                        <h4>¥{{ number_format($stats['today_sales'], 2) }}</h4>
                        <p class="text-muted mb-0">今日销售额</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fa fa-calendar-o me-2"></i>昨日数据</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4>{{ $stats['yesterday_orders'] }}</h4>
                        <p class="text-muted mb-0">昨日订单</p>
                    </div>
                    <div class="col-6">
                        <h4>¥{{ number_format($stats['yesterday_sales'], 2) }}</h4>
                        <p class="text-muted mb-0">昨日销售额</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- 订单趋势图 -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">近7天订单趋势</h5>
            </div>
            <div class="card-body">
                <div id="orderTrendChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- 平台订单分布 -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">各平台订单分布</h5>
            </div>
            <div class="card-body">
                <div id="platformChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- 库存预警 -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-white"><i class="fa fa-exclamation-triangle me-2"></i>库存预警</h5>
            </div>
            <div class="card-body">
                @if($lowStockItems->isEmpty())
                    <p class="text-muted mb-0">暂无库存预警</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>商品</th>
                                    <th>仓库</th>
                                    <th>库存</th>
                                    <th>预警值</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems as $item)
                                <tr>
                                    <td>{{ $item->sku->name ?? ($item->sku->sku_code ?? 'N/A') }}</td>
                                    <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-danger">{{ $item->actual_quantity }}</span></td>
                                    <td>{{ $item->safety_quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 最近订单 -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">最近订单</h5>
            </div>
            <div class="card-body">
                @if($recentOrders->isEmpty())
                    <p class="text-muted mb-0">暂无订单</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>店铺</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->shop->name ?? 'N/A' }}</td>
                                    <td>¥{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- 同步状态 -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-refresh me-2"></i>同步状态</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>最后订单同步：</strong></p>
                        <p class="text-muted">
                            @if($syncStatus['last_order_sync'])
                                {{ $syncStatus['last_order_sync']->created_at }}
                                <span class="badge bg-{{ $syncStatus['last_order_sync']->status == 'success' ? 'success' : 'danger' }}">
                                    {{ $syncStatus['last_order_sync']->status }}
                                </span>
                            @else
                                从未同步
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>最后库存同步：</strong></p>
                        <p class="text-muted">
                            @if($syncStatus['last_stock_sync'])
                                {{ $syncStatus['last_stock_sync']->created_at }}
                                <span class="badge bg-{{ $syncStatus['last_stock_sync']->status == 'success' ? 'success' : 'danger' }}">
                                    {{ $syncStatus['last_stock_sync']->status }}
                                </span>
                            @else
                                从未同步
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>待处理售后：</strong></p>
                        <p class="text-muted">
                            {{ $stats['pending_aftersale'] }} 条
                            <a href="{{ route('soho.aftersale.index') }}" class="btn btn-sm btn-outline-primary">查看</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/soho/js/echarts.min.js') }}"></script>
<script>
    // 订单趋势图
    var orderTrendChart = echarts.init(document.getElementById('orderTrendChart'));
    var orderData = @json($last7Days);
    var dates = orderData.map(function(item) { return item.date; });
    var orderCounts = orderData.map(function(item) { return item.count; });
    var orderAmounts = orderData.map(function(item) { return (item.amount / 100).toFixed(2); });

    orderTrendChart.setOption({
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['订单数', '销售额(百元)']
        },
        xAxis: {
            type: 'category',
            data: dates
        },
        yAxis: [
            { type: 'value', name: '订单数' },
            { type: 'value', name: '销售额' }
        ],
        series: [
            {
                name: '订单数',
                type: 'line',
                data: orderCounts,
                itemStyle: { color: '#667eea' }
            },
            {
                name: '销售额(百元)',
                type: 'line',
                yAxisIndex: 1,
                data: orderAmounts,
                itemStyle: { color: '#28a745' }
            }
        ]
    });

    // 平台订单分布图
    var platformChart = echarts.init(document.getElementById('platformChart'));
    var platformData = @json($platformOrderStats);
    var platformNames = platformData.map(function(item) { return item.platform; });
    var platformCounts = platformData.map(function(item) { return item.count; });

    platformChart.setOption({
        tooltip: {
            trigger: 'item',
            formatter: '{b}: {c} ({d}%)'
        },
        series: [
            {
                type: 'pie',
                radius: ['40%', '70%'],
                data: platformData.map(function(item, index) {
                    return { value: item.count, name: item.platform };
                })
            }
        ]
    });

    // 窗口大小改变时自适应
    window.addEventListener('resize', function() {
        orderTrendChart.resize();
        platformChart.resize();
    });
</script>
@endsection