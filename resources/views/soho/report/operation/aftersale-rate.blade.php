@extends('soho::layouts.app')

{{-- 售后率报表视图 --}}
@section('title', '售后率报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">售后率报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">售后率</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.operation.aftersale.rate') }}" class="row g-2 align-items-end">
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
                    <a href="{{ route('soho.operation.aftersale.rate') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>平均退款率</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_refund_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>平均退货率</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_return_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>平均投诉率</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_dispute_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">各店铺售后率对比</h5></div>
        <div class="card-body">
            <div id="rate-chart" style="width: 100%; height: 350px;"></div>
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
                        <th class="text-end">退款率</th>
                        <th class="text-end">退货率</th>
                        <th class="text-end">投诉率</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $platformNames = [
                            'taobao' => '淘宝', 'jd' => '京东', 'pdd' => '拼多多',
                            'douyin' => '抖音', 'amazon' => '亚马逊',
                        ];
                        // 售后率颜色映射函数
                        $rateColor = function($rate, $thresholds = [5, 10]) {
                            if ($rate >= $thresholds[1]) return 'danger';
                            if ($rate >= $thresholds[0]) return 'warning';
                            return 'success';
                        };
                    @endphp
                    @forelse($report as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $item->shop_name }}</td>
                            <td><span class="badge bg-secondary">{{ $platformNames[$item->platform] ?? $item->platform }}</span></td>
                            <td class="text-end">{{ number_format($item->order_count) }}</td>
                            <td class="text-end"><span class="badge bg-{{ $rateColor($item->refund_rate) }}">{{ number_format($item->refund_rate, 2) }}%</span></td>
                            <td class="text-end"><span class="badge bg-{{ $rateColor($item->return_rate) }}">{{ number_format($item->return_rate, 2) }}%</span></td>
                            <td class="text-end"><span class="badge bg-{{ $rateColor($item->dispute_rate, [1, 3]) }}">{{ number_format($item->dispute_rate, 2) }}%</span></td>
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
        @if($report->hasPages())
            <div class="card-footer">{{ $report->links() }}</div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDom = document.getElementById('rate-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            var shops = {!! json_encode(collect($report->items())->pluck('shop_name')->values()) !!};
            var refundRates = {!! json_encode(collect($report->items())->pluck('refund_rate')->values()) !!};
            var returnRates = {!! json_encode(collect($report->items())->pluck('return_rate')->values()) !!};
            var disputeRates = {!! json_encode(collect($report->items())->pluck('dispute_rate')->values()) !!};

            var option = {
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                legend: { data: ['退款率', '退货率', '投诉率'] },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', data: shops, axisLabel: { rotate: 30 } },
                yAxis: { type: 'value', name: '百分比(%)' },
                series: [
                    { name: '退款率', type: 'bar', data: refundRates, itemStyle: { color: '#dc3545' } },
                    { name: '退货率', type: 'bar', data: returnRates, itemStyle: { color: '#ffc107' } },
                    { name: '投诉率', type: 'bar', data: disputeRates, itemStyle: { color: '#17a2b8' } }
                ]
            };
            chart.setOption(option);
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
