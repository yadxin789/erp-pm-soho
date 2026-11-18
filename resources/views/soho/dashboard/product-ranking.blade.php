@extends('soho::layouts.app')

@section('title', '商品排名')

@section('content')
<!-- 筛选表单 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('soho.product.ranking') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">统计周期</label>
                <select name="period" class="form-select">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>今日</option>
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>本周</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>本月</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>本年</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">排序方式</label>
                <select name="sort_by" class="form-select">
                    <option value="sales" {{ $sortBy == 'sales' ? 'selected' : '' }}>按销售额</option>
                    <option value="count" {{ $sortBy == 'count' ? 'selected' : '' }}>按订单数</option>
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

<!-- TOP 10 热销商品 -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="fa fa-trophy me-2"></i>热销商品 TOP 10</h5>
            </div>
            <div class="card-body">
                <div id="topProductsChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">分类销售统计</h5>
            </div>
            <div class="card-body">
                <div id="categoryChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- 商品排名明细 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">商品排名明细</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>排名</th>
                        <th>商品名称</th>
                        <th>品牌</th>
                        <th>分类</th>
                        <th>订单数</th>
                        <th>销售数量</th>
                        <th>销售金额</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productRankings as $index => $item)
                    @php
                        $product = $products[$item->product_id] ?? null;
                    @endphp
                    <tr>
                        <td>
                            @if($index < 3)
                                <span class="badge bg-{{ ['danger', 'warning', 'info'][$index] }} fs-6">
                                    {{ $index + 1 }}
                                </span>
                            @else
                                <span class="text-muted">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td>{{ $product->name ?? '未知商品' }}</td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ $item->order_count }}</td>
                        <td>{{ $item->total_quantity }}</td>
                        <td class="text-success">¥{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">暂无销售数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/soho/js/echarts.min.js') }}"></script>
<script>
    // TOP 10 商品图表
    var topProductsChart = echarts.init(document.getElementById('topProductsChart'));
    var topProductsData = @json($topProducts);
    var productMap = @json($products->map(fn($p) => ['name' => $p->name, 'brand' => $p->brand->name ?? ''])->toArray());
    
    topProductsChart.setOption({
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: { type: 'value', name: '销售额' },
        yAxis: { 
            type: 'category', 
            data: topProductsData.map(function(item) { 
                var p = productMap.find(function(p) { return p.id === item.product_id; });
                return p ? p.name : '未知商品';
            }).reverse()
        },
        series: [{
            type: 'bar',
            data: topProductsData.map(d => d.total_amount).reverse(),
            itemStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
                    { offset: 0, color: '#667eea' },
                    { offset: 1, color: '#764ba2' }
                ])
            },
            label: { show: true, position: 'right', formatter: '¥{c}' }
        }]
    });

    // 分类销售图表
    var categoryChart = echarts.init(document.getElementById('categoryChart'));
    var categoryData = @json($categoryStats);
    
    categoryChart.setOption({
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
        series: [{
            type: 'pie',
            radius: ['40%', '70%'],
            data: categoryData.map(c => ({ value: c.count, name: c.category ?? '未分类' }))
        }]
    });

    window.addEventListener('resize', function() {
        topProductsChart.resize();
        categoryChart.resize();
    });
</script>
@endsection
