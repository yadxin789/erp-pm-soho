@extends('soho::layouts.app')

{{-- 店铺分析视图 --}}
@section('title', '店铺分析')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">店铺分析</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">数据报表</li>
                    <li class="breadcrumb-item active">店铺分析</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.shop.analysis') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">平台</label>
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platformOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('platform') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.shop.analysis') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>店铺总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_shops'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>订单总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>总销售额</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_sales'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>总退款金额</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_refund'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>平均客单价</small>
                    <h3 class="mb-0">¥{{ number_format($stats['avg_aov'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">各店铺销售额对比</h5></div>
        <div class="card-body">
            <div id="sales-chart" style="width: 100%; height: 350px;"></div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>店铺名称</th>
                        <th>平台</th>
                        <th class="text-end">订单数</th>
                        <th class="text-end">销售额</th>
                        <th class="text-end">客单价</th>
                        <th class="text-end">转化率</th>
                        <th class="text-end">退款率</th>
                        <th class="text-end">完成率</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $platformNames = [
                            'taobao' => '淘宝', 'jd' => '京东', 'pdd' => '拼多多',
                            'douyin' => '抖音', 'amazon' => '亚马逊',
                        ];
                    @endphp
                    @forelse($report as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $item->shop_name }}</td>
                            <td><span class="badge bg-secondary">{{ $platformNames[$item->platform] ?? $item->platform }}</span></td>
                            <td class="text-end">{{ number_format($item->order_count) }}</td>
                            <td class="text-end">¥{{ number_format($item->sales_amount, 2) }}</td>
                            <td class="text-end">¥{{ number_format($item->avg_order_value, 2) }}</td>
                            <td class="text-end">
                                @php $c = $item->conversion_rate >= 80 ? 'success' : ($item->conversion_rate >= 50 ? 'warning' : 'danger'); @endphp
                                <span class="badge bg-{{ $c }}">{{ number_format($item->conversion_rate, 2) }}%</span>
                            </td>
                            <td class="text-end">
                                @php $c = $item->refund_rate >= 10 ? 'danger' : ($item->refund_rate >= 5 ? 'warning' : 'success'); @endphp
                                <span class="badge bg-{{ $c }}">{{ number_format($item->refund_rate, 2) }}%</span>
                            </td>
                            <td class="text-end">
                                @php $c = $item->completion_rate >= 80 ? 'success' : ($item->completion_rate >= 50 ? 'warning' : 'danger'); @endphp
                                <span class="badge bg-{{ $c }}">{{ number_format($item->completion_rate, 2) }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($report->total() > 0)
            <div class="card-footer">{{ $report->links() }}</div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDom = document.getElementById('sales-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            var shops = {!! json_encode(collect($report->items())->pluck('shop_name')->values()) !!};
            var sales = {!! json_encode(collect($report->items())->pluck('sales_amount')->map(fn($v) => round($v ?? 0, 2))->values()) !!};
            var refunds = {!! json_encode(collect($report->items())->pluck('refund_amount')->map(fn($v) => round($v ?? 0, 2))->values()) !!};

            chart.setOption({
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                legend: { data: ['销售额', '退款金额'] },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', data: shops, axisLabel: { rotate: 30 } },
                yAxis: { type: 'value', name: '金额(¥)', axisLabel: { formatter: '¥{value}' } },
                series: [
                    { name: '销售额', type: 'bar', data: sales, itemStyle: { color: '#28a745' } },
                    { name: '退款金额', type: 'bar', data: refunds, itemStyle: { color: '#dc3545' } }
                ]
            });
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
