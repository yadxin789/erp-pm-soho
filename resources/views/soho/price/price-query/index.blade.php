@extends('soho::layouts.app')

@section('title', '价格查询')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">价格中心</a></li>
        <li class="breadcrumb-item active">价格查询</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-search me-2"></i>价格查询</h4>
</div>

{{-- 统计卡片 --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small mb-1">商品总数</div>
                <div class="display-5 text-primary">{{ $statistics['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small mb-1">平均价格</div>
                <div class="display-5 text-info">¥{{ number_format($statistics['avg_price'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small mb-1">最低价格</div>
                <div class="display-5 text-success">¥{{ number_format($statistics['min_price'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small mb-1">最高价格</div>
                <div class="display-5 text-warning">¥{{ number_format($statistics['max_price'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- 搜索/筛选表单 --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('soho.price.query.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">商品名称/SKU</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="输入商品名称或SKU编码">
            </div>
            <div class="col-md-2">
                <label class="form-label">分类</label>
                <select class="form-select" name="category_id">
                    <option value="">全部分类</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">平台</label>
                <select class="form-select" name="platform">
                    <option value="">全部平台</option>
                    @foreach($platforms ?? [] as $platform)
                        <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ $platform }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">最低价格</label>
                <input type="number" step="0.01" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="¥0.00" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">最高价格</label>
                <input type="number" step="0.01" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="不限" min="0">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-1">
                    <i class="fa fa-search"></i>
                </button>
                <a href="{{ route('soho.price.query.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 商品价格表格 --}}
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-list me-2"></i>商品价格列表</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>商品名称</th>
                        <th>SKU</th>
                        <th>分类</th>
                        <th>平台</th>
                        <th>当前价格</th>
                        <th>成本价</th>
                        <th>利润率</th>
                        <th width="100">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                @if($product->product_code)
                                    <div class="small text-muted">{{ $product->product_code }}</div>
                                @endif
                            </td>
                            <td>
                                @if($product->skus && $product->skus->count() > 0)
                                    <span class="badge bg-light text-dark">{{ $product->skus->count() }} 个SKU</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($product->category)
                                    <span class="badge bg-info">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($product->platform)
                                    <span class="badge bg-light text-dark">{{ $product->platform }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-primary">
                                ¥{{ number_format($product->current_price ?? 0, 2) }}
                            </td>
                            <td class="text-muted">
                                ¥{{ number_format($product->cost_price ?? 0, 2) }}
                            </td>
                            <td>
                                @php
                                    $profit = $product->current_price - $product->cost_price;
                                    $profitRate = $product->cost_price > 0 ? ($profit / $product->cost_price) * 100 : 0;
                                @endphp
                                @if($profitRate >= 30)
                                    <span class="badge bg-success">{{ number_format($profitRate, 1) }}%</span>
                                @elseif($profitRate >= 10)
                                    <span class="badge bg-warning text-dark">{{ number_format($profitRate, 1) }}%</span>
                                @else
                                    <span class="badge bg-danger">{{ number_format($profitRate, 1) }}%</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('soho.price.queryshow', $product) }}" class="btn btn-sm btn-outline-primary" title="查看详情">
                                    <i class="fa fa-eye"></i>
                                </a>
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
    </div>
    @if(method_exists($products, 'links'))
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection