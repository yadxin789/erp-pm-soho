@extends('soho::layouts.app')

{{-- 库存周转分析视图 --}}
@section('title', '库存周转分析')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">库存周转分析</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">数据报表</li>
                    <li class="breadcrumb-item active">库存周转分析</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.inventory.turnover') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">仓库</label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.inventory.turnover') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>SKU总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_skus'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>当前总库存</small>
                    <h3 class="mb-0">{{ number_format($stats['total_stock'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>期间总销量</small>
                    <h3 class="mb-0">{{ number_format($stats['total_sold'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>平均周转率</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_turnover'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>平均周转天数</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_turnover_days'] ?? 0, 2) }} 天</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">库存周转率分布</h5></div>
        <div class="card-body">
            <div id="turnover-chart" style="width: 100%; height: 350px;"></div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>SKU</th>
                        <th>商品名称</th>
                        <th>仓库</th>
                        <th class="text-end">当前库存</th>
                        <th class="text-end">期间销量</th>
                        <th class="text-end">销售成本</th>
                        <th class="text-end">周转率</th>
                        <th class="text-end">周转天数</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->sku_code ?? '-' }}</code></td>
                            <td>{{ $item->product_name ?? '-' }}</td>
                            <td>{{ $item->warehouse_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->current_stock) }}</td>
                            <td class="text-end">{{ number_format($item->sold_quantity) }}</td>
                            <td class="text-end">¥{{ number_format($item->cost_amount, 2) }}</td>
                            <td class="text-end">
                                @php
                                    $color = $item->turnover_rate >= 1 ? 'success' : ($item->turnover_rate >= 0.3 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ number_format($item->turnover_rate, 2) }}</span>
                            </td>
                            <td class="text-end">{{ number_format($item->turnover_days, 2) }} 天</td>
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
        @if($report->hasPages())
            <div class="card-footer">{{ $report->links() }}</div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDom = document.getElementById('turnover-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            var data = {!! json_encode(collect($report->items())->take(15)->values()->map(function($item) {
                return ['name' => $item->sku_code ?? $item->product_name ?? '-', 'turnover' => $item->turnover_rate ?? 0, 'days' => $item->turnover_days ?? 0];
            })) !!};

            chart.setOption({
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                legend: { data: ['周转率', '周转天数'] },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', data: data.map(d => d.name), axisLabel: { rotate: 30 } },
                yAxis: [
                    { type: 'value', name: '周转率', position: 'left' },
                    { type: 'value', name: '天数', position: 'right' }
                ],
                series: [
                    { name: '周转率', type: 'bar', data: data.map(d => d.turnover), itemStyle: { color: '#28a745' } },
                    { name: '周转天数', type: 'line', yAxisIndex: 1, data: data.map(d => d.days), smooth: true, itemStyle: { color: '#dc3545' } }
                ]
            });
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
