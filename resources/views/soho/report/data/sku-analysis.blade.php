@extends('soho::layouts.app')

{{-- SKU分析视图 --}}
@section('title', 'SKU分析')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">SKU分析</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">数据报表</li>
                    <li class="breadcrumb-item active">SKU分析</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.sku.analysis') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">关键词</label>
                    <input type="text" name="keyword" class="form-control" placeholder="SKU编码 / 商品名称" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.sku.analysis') }}" class="btn btn-outline-secondary">重置</a>
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
                    <small>总销量</small>
                    <h3 class="mb-0">{{ number_format($stats['total_quantity'] ?? 0) }}</h3>
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
                    <small>总利润</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_profit'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>平均利润率</small>
                    <h3 class="mb-0">{{ number_format($stats['avg_profit_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">销量TOP10 SKU</h5></div>
        <div class="card-body">
            <div id="top-chart" style="width: 100%; height: 350px;"></div>
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
                        <th class="text-end">销量</th>
                        <th class="text-end">销售额</th>
                        <th class="text-end">成本</th>
                        <th class="text-end">利润</th>
                        <th class="text-end">利润率</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->sku_code ?? '-' }}</code></td>
                            <td>{{ $item->product_name ?? $item->sku_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->sold_quantity) }}</td>
                            <td class="text-end">¥{{ number_format($item->sales_amount, 2) }}</td>
                            <td class="text-end">¥{{ number_format($item->cost_amount, 2) }}</td>
                            <td class="text-end text-success">¥{{ number_format($item->profit_amount, 2) }}</td>
                            <td class="text-end">
                                @php
                                    $color = $item->profit_margin >= 30 ? 'success' : ($item->profit_margin >= 10 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ number_format($item->profit_margin, 2) }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
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
            var chartDom = document.getElementById('top-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            // 取销量前10的SKU
            var topData = {!! json_encode(collect($report->items())->take(10)->values()->map(function($item) {
                return ['name' => $item->sku_code ?? $item->product_name ?? '-', 'value' => $item->sold_quantity ?? 0];
            })) !!};

            chart.setOption({
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'value', name: '销量' },
                yAxis: { type: 'category', data: topData.map(d => d.name).reverse(), axisLabel: { rotate: 0 } },
                series: [{
                    type: 'bar',
                    data: topData.map(d => d.value).reverse(),
                    itemStyle: { color: '#667eea' },
                    label: { show: true, position: 'right' }
                }]
            });
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
