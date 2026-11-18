@extends('soho::layouts.app')

@section('title', '库存概览')

@section('content')
<!-- 库存统计卡片 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">商品种类</h6>
                <h3 class="text-primary">{{ number_format($inventoryStats['total_items']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">库存总量</h6>
                <h3 class="text-success">{{ number_format($inventoryStats['total_quantity']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">库存预警</h6>
                <h3 class="text-warning">{{ $inventoryStats['low_stock_count'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">缺货商品</h6>
                <h3 class="text-danger">{{ $inventoryStats['out_of_stock_count'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- 仓库分布图表 -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">仓库库存分布</h5>
            </div>
            <div class="card-body">
                <div id="warehouseChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-white"><i class="fa fa-exclamation-triangle me-2"></i>低库存预警 TOP 15</h5>
            </div>
            <div class="card-body">
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
                            @forelse($lowStockAlerts as $item)
                            <tr>
                                <td>{{ $item->sku->name ?? ($item->sku->sku_code ?? 'N/A') }}</td>
                                <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-danger">{{ $item->actual_quantity }}</span></td>
                                <td>{{ $item->safety_quantity }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">暂无库存预警</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 库存列表 -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库存详情</h5>
        <form method="GET" action="{{ route('soho.inventory.index') }}" class="d-flex gap-2">
            <select name="warehouse_id" class="form-select form-select-sm">
                <option value="">全部仓库</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary">筛选</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>商品名称</th>
                        <th>SKU</th>
                        <th>仓库</th>
                        <th>库存</th>
                        <th>预警值</th>
                        <th>状态</th>
                        <th>更新时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $item)
                    <tr>
                        <td>{{ $item->sku->product->name ?? ($item->sku->name ?? 'N/A') }}</td>
                        <td>{{ $item->sku->sku_code ?? 'N/A' }}</td>
                        <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                        <td class="{{ $item->actual_quantity <= $item->safety_quantity && $item->safety_quantity > 0 ? 'text-danger fw-bold' : '' }}">{{ $item->actual_quantity }}</td>
                        <td>{{ $item->safety_quantity }}</td>
                        <td>
                            @if($item->actual_quantity == 0)
                                <span class="badge bg-danger">缺货</span>
                            @elseif($item->safety_quantity > 0 && $item->actual_quantity <= $item->safety_quantity)
                                <span class="badge bg-warning">库存不足</span>
                            @else
                                <span class="badge bg-success">正常</span>
                            @endif
                        </td>
                        <td>{{ $item->updated_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">暂无库存数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $inventory->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/soho/js/echarts.min.js') }}"></script>
<script>
    // 仓库库存分布图
    var warehouseChart = echarts.init(document.getElementById('warehouseChart'));
    var warehouseData = @json($warehouseStats->map(function($w) {
        return ['warehouse' => $w->warehouse->name ?? '未知', 'items' => $w->items, 'qty' => $w->total_qty];
    }));
    
    warehouseChart.setOption({
        tooltip: { trigger: 'axis' },
        legend: { data: ['商品数', '库存数量'] },
        xAxis: { type: 'category', data: warehouseData.map(w => w.warehouse) },
        yAxis: { type: 'value' },
        series: [
            { name: '商品数', type: 'bar', data: warehouseData.map(w => w.items), itemStyle: { color: '#667eea' } },
            { name: '库存数量', type: 'bar', data: warehouseData.map(w => w.qty), itemStyle: { color: '#28a745' } }
        ]
    });

    window.addEventListener('resize', function() {
        warehouseChart.resize();
    });
</script>
@endsection
