@extends('soho::layouts.app')

{{-- 订单数据分析视图 --}}
@section('title', '订单数据分析')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">订单数据分析</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">数据报表</li>
                    <li class="breadcrumb-item active">订单数据分析</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.order') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">店铺</label>
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">平台</label>
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platformOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('platform') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.order') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>订单总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>总销售额</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_sales'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>客单价</small>
                    <h3 class="mb-0">¥{{ number_format($stats['avg_order_value'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>支付转化率</small>
                    <h3 class="mb-0">{{ number_format($stats['conversion_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>完成率</small>
                    <h3 class="mb-0">{{ number_format($stats['completion_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body py-3">
                    <small>日均订单数</small>
                    <h3 class="mb-0">{{ number_format($stats['daily_avg'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域：订单趋势 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">订单趋势分析</h5></div>
        <div class="card-body">
            <div id="trend-chart" style="width: 100%; height: 350px;"></div>
        </div>
    </div>

    {{-- 图表占位区域：平台占比 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">各平台销售占比</h5></div>
        <div class="card-body">
            <div id="platform-chart" style="width: 100%; height: 300px;"></div>
        </div>
    </div>

    {{-- 数据表格：订单明细 --}}
    <div class="card">
        <div class="card-header"><h5 class="mb-0">每日订单数据</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>日期</th>
                        <th class="text-end">订单数</th>
                        <th class="text-end">销售额</th>
                        <th class="text-end">已支付</th>
                        <th class="text-end">已完成</th>
                        <th class="text-end">转化率</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trend as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $item->date }}</td>
                            <td class="text-end">{{ number_format($item->order_count) }}</td>
                            <td class="text-end">¥{{ number_format($item->sales_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->paid_count) }}</td>
                            <td class="text-end">{{ number_format($item->completed_count) }}</td>
                            <td class="text-end">
                                @php
                                    $rate = $item->order_count > 0 ? round($item->paid_count / $item->order_count * 100, 2) : 0;
                                    $color = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ number_format($rate, 2) }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 订单趋势图
            var trendDom = document.getElementById('trend-chart');
            if (trendDom && typeof echarts !== 'undefined') {
                var trendChart = echarts.init(trendDom);
                var dates = {!! json_encode(collect($trend)->pluck('date')->values()) !!};
                var orderCounts = {!! json_encode(collect($trend)->pluck('order_count')->values()) !!};
                var salesAmounts = {!! json_encode(collect($trend)->pluck('sales_amount')->map(fn($v) => round($v ?? 0, 2))->values()) !!};

                trendChart.setOption({
                    tooltip: { trigger: 'axis', axisPointer: { type: 'cross' } },
                    legend: { data: ['订单数', '销售额'] },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: { type: 'category', data: dates, boundaryGap: false },
                    yAxis: [
                        { type: 'value', name: '订单数', position: 'left' },
                        { type: 'value', name: '销售额(¥)', position: 'right', axisLabel: { formatter: '¥{value}' } }
                    ],
                    series: [
                        { name: '订单数', type: 'bar', data: orderCounts, itemStyle: { color: '#667eea' } },
                        { name: '销售额', type: 'line', yAxisIndex: 1, data: salesAmounts, smooth: true, itemStyle: { color: '#28a745' } }
                    ]
                });
                window.addEventListener('resize', function() { trendChart.resize(); });
            }

            // 平台占比图
            var platformDom = document.getElementById('platform-chart');
            if (platformDom && typeof echarts !== 'undefined') {
                var platformChart = echarts.init(platformDom);
                var platformData = {!! json_encode(collect($platformStats)->map(function($item) {
                    $names = ['taobao' => '淘宝', 'jd' => '京东', 'pdd' => '拼多多', 'douyin' => '抖音', 'amazon' => '亚马逊'];
                    return ['name' => $names[$item->platform] ?? $item->platform, 'value' => round($item->sales_amount ?? 0, 2)];
                })->values()) !!};

                platformChart.setOption({
                    tooltip: { trigger: 'item', formatter: '{a} <br/>{b}: ¥{c} ({d}%)' },
                    legend: { orient: 'vertical', left: 'left' },
                    series: [{
                        name: '平台销售', type: 'pie', radius: '50%', data: platformData,
                        emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.5)' } }
                    }]
                });
                window.addEventListener('resize', function() { platformChart.resize(); });
            }
        });
    </script>
@endsection
