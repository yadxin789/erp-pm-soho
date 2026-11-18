@extends('soho::layouts.app')
@section('title', '库存管理')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库存列表</h5>
        <a href="{{ route('soho.inventory.export') }}" class="btn btn-success"><i class="fa fa-download"></i> 导出</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>商品</th><th>SKU</th><th>仓库</th><th>库存</th><th>预警值</th><th>状态</th></tr></thead>
                <tbody>
                    @forelse($inventories as $inv)
                    <tr>
                        <td>{{ $inv->sku->product->name ?? ($inv->sku->name ?? '-') }}</td>
                        <td>{{ $inv->sku->sku_code ?? '-' }}</td>
                        <td>{{ $inv->warehouse->name ?? '-' }}</td>
                        <td><span class="badge bg-{{ $inv->actual_quantity <= $inv->safety_quantity && $inv->safety_quantity > 0 ? 'danger' : 'success' }}">{{ $inv->actual_quantity }}</span></td>
                        <td>{{ $inv->safety_quantity }}</td>
                        <td>{{ $inv->safety_quantity > 0 && $inv->actual_quantity <= $inv->safety_quantity ? '库存不足' : '正常' }}</td>
                    </tr>
                    @empty<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $inventories->links() }}
    </div>
</div>
@endsection
