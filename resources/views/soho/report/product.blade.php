@extends('soho::layouts.app')
@section('title', '商品报表')
@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">筛选条件</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.report.product') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">开始日期</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-d', strtotime('-30 days'))) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">结束日期</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">店铺</label>
                <select name="shop_id" class="form-select">
                    <option value="">全部店铺</option>
                    @foreach($shops ?? [] as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2"><i class="fa fa-search"></i> 查询</button>
                <a href="{{ route('soho.report.product') }}" class="btn btn-secondary">重置</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">统计概览</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-primary mb-0">{{ $stats['total_products'] ?? 0 }}</h3>
                        <p class="text-muted mb-0 mt-2">商品总数</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-success mb-0">{{ $stats['on_sale_count'] ?? 0 }}</h3>
                        <p class="text-muted mb-0 mt-2">在售商品数</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-danger mb-0">{{ $stats['out_of_stock_count'] ?? 0 }}</h3>
                        <p class="text-muted mb-0 mt-2">缺货商品数</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">商品排行</h5>
        <a href="{{ route('soho.report.product.export', request()->query()) }}" class="btn btn-success"><i class="fa fa-download"></i> 导出</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>排名</th>
                        <th>商品名称</th>
                        <th>SKU</th>
                        <th class="text-end">销量</th>
                        <th class="text-end">销售额</th>
                        <th class="text-end">库存</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                        <tr>
                            <td>
                                <span class="badge {{ $index < 3 ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td>{{ $product->name }}</td>
                            <td><code>{{ $product->sku }}</code></td>
                            <td class="text-end">{{ number_format($product->sales ?? 0) }}</td>
                            <td class="text-end">¥{{ number_format($product->revenue ?? 0, 2) }}</td>
                            <td class="text-end">
                                <span class="badge {{ ($product->stock ?? 0) == 0 ? 'bg-danger' : (($product->stock ?? 0) < 10 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $product->stock ?? 0 }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">暂无数据</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->total() > 0)
            <div class="d-flex justify-content-center mt-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection