@extends('soho::layouts.app')

{{-- 发货及时率报表视图 --}}
@section('title', '发货及时率报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">发货及时率报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">发货及时率</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.operation.ship.on-time') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">店铺</label>
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.operation.ship.on-time') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>已发货订单数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_shipped'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>及时发货数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_on_time'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>整体及时率</small>
                    <h3 class="mb-0">{{ number_format($stats['overall_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>平均发货时长</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_ship_hours'] ?? 0, 2) }} 小时</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">各店铺发货及时率对比</h5></div>
        <div class="card-body">
            <div id="ship-rate-chart" style="width: 100%; height: 300px;"></div>
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
                        <th class="text-end">已发货数</th>
                        <th class="text-end">及时发货数</th>
                        <th class="text-end">及时率</th>
                        <th class="text-end">平均发货时长</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $item->shop_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->shipped_count) }}</td>
                            <td class="text-end text-success">{{ number_format($item->on_time_count) }}</td>
                            <td class="text-end">
                                @php
                                    $color = $item->on_time_rate >= 95 ? 'success' : ($item->on_time_rate >= 80 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ number_format($item->on_time_rate, 2) }}%</span>
                            </td>
                            <td class="text-end">{{ number_format($item->avg_ship_hours, 2) }} 小时</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
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
            var chartDom = document.getElementById('ship-rate-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            var data = {!! json_encode(collect($report->items())->map(function($item) {
                return ['name' => $item->shop_name ?? '-', 'value' => $item->on_time_rate];
            })->values()) !!};

            var option = {
                tooltip: { trigger: 'axis', formatter: '{b}: {c}%' },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', data: data.map(d => d.name), axisLabel: { rotate: 30 } },
                yAxis: { type: 'value', name: '及时率(%)', max: 100 },
                series: [{
                    type: 'bar',
                    data: data.map(d => d.value),
                    itemStyle: { color: '#28a745' },
                    label: { show: true, position: 'top', formatter: '{c}%' }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
