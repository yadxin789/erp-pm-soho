@extends('soho::layouts.app')

{{-- 运营仪表盘视图 --}}
@section('title', '运营仪表盘')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">运营仪表盘</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">运营仪表盘</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.operation.dashboard') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">店铺筛选</label>
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.operation.dashboard') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>今日订单数</small>
                            <h3 class="mb-0">{{ number_format($stats['today_orders'] ?? 0) }}</h3>
                        </div>
                        <i class="fa fa-shopping-cart fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>待发货数</small>
                            <h3 class="mb-0">{{ number_format($stats['pending_ship'] ?? 0) }}</h3>
                        </div>
                        <i class="fa fa-box fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>待退款数</small>
                            <h3 class="mb-0">{{ number_format($stats['pending_refund'] ?? 0) }}</h3>
                        </div>
                        <i class="fa fa-undo fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>异常订单数</small>
                            <h3 class="mb-0">{{ number_format($stats['abnormal_orders'] ?? 0) }}</h3>
                        </div>
                        <i class="fa fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">近7天订单趋势</h5>
        </div>
        <div class="card-body">
            <div id="trend-chart" style="width: 100%; height: 350px;"></div>
        </div>
    </div>

    {{-- 异常订单待办列表 --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">异常订单待办</h5>
            <a href="{{ route('soho.operation.order.exception') }}" class="btn btn-sm btn-outline-primary">查看全部</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>订单号</th>
                        <th>异常原因</th>
                        <th>订单状态</th>
                        <th>下单时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAbnormal as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->platform_order_no }}</code></td>
                            <td><span class="badge bg-danger">{{ $item->abnormal_reason ?? '未标注' }}</span></td>
                            <td>{{ $item->order_status }}</td>
                            <td class="small">{{ optional($item->order_created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无异常订单</div>
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
            var chartDom = document.getElementById('trend-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            var dates = {!! json_encode(collect($trend)->pluck('date')->values()) !!};
            var orderCounts = {!! json_encode(collect($trend)->pluck('order_count')->map(fn($v) => $v ?? 0)->values()) !!};
            var salesAmounts = {!! json_encode(collect($trend)->pluck('sales_amount')->map(fn($v) => round($v ?? 0, 2))->values()) !!};

            var option = {
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
            };
            chart.setOption(option);
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
