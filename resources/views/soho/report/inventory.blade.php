@extends('soho::layouts.app')
@section('title', '库存报表')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库存报表</h5>
        <a href="{{ route('soho.report.inventory.export') }}" class="btn btn-success"><i class="fa fa-download"></i> 导出</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ $stats['total_items'] }}</h5><p class="mb-0 text-muted">总条目</p></div></div></div>
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ $stats['total_quantity'] }}</h5><p class="mb-0 text-muted">总库存</p></div></div></div>
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-warning">{{ $stats['low_stock_count'] }}</h5><p class="mb-0 text-muted">库存不足</p></div></div></div>
            <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-danger">{{ $stats['out_of_stock_count'] }}</h5><p class="mb-0 text-muted">缺货</p></div></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>商品</th><th>SKU</th><th>仓库</th><th>库存</th><th>预警值</th><th>状态</th><th>更新时间</th></tr></thead>
                <tbody>
                    @forelse($report as $item)
                    <tr>
                        <td>{{ $item->sku->product->name ?? ($item->sku->name ?? '-') }}</td>
                        <td>{{ $item->sku->sku_code ?? '-' }}</td>
                        <td>{{ $item->warehouse->name ?? '-' }}</td>
                        <td><span class="badge bg-{{ $item->actual_quantity == 0 ? 'danger' : ($item->safety_quantity > 0 && $item->actual_quantity <= $item->safety_quantity ? 'warning' : 'success') }}">{{ $item->actual_quantity }}</span></td>
                        <td>{{ $item->safety_quantity }}</td>
                        <td>{{ $item->actual_quantity == 0 ? '缺货' : ($item->safety_quantity > 0 && $item->actual_quantity <= $item->safety_quantity ? '库存不足' : '正常') }}</td>
                        <td>{{ $item->updated_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty<tr><td colspan="7" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $report->links() }}
    </div>
</div>
@endsection