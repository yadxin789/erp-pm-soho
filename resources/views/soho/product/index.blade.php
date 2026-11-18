@extends('soho::layouts.app')

@section('title', '商品 SPU 管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">商品 SPU 列表</h5>
        <a href="{{ route('soho.product.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加商品
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索商品名称/编码/条码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">全部分类</option>
                        @foreach(\App\Soho\Models\ProductCategory::all() as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="brand_id" class="form-select">
                        <option value="">全部品牌</option>
                        @foreach(\App\Soho\Models\Brand::all() as $brand)
                        <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.product.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名称</th>
                        <th>商品编码</th>
                        <th>品牌</th>
                        <th>分类</th>
                        <th>SKU数</th>
                        <th>成本价</th>
                        <th>零售价</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->product_code }}</td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $product->skus->count() }}</span></td>
                        <td>¥{{ number_format($product->cost_price ?? 0, 2) }}</td>
                        <td>¥{{ number_format($product->retail_price ?? 0, 2) }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    'draft' => ['label' => '草稿', 'class' => 'secondary'],
                                    'pending' => ['label' => '待审核', 'class' => 'warning'],
                                    'active' => ['label' => '启用', 'class' => 'success'],
                                    'inactive' => ['label' => '停用', 'class' => 'danger'],
                                    'archived' => ['label' => '已归档', 'class' => 'info'],
                                    'error' => ['label' => '异常', 'class' => 'dark'],
                                ];
                                $statusInfo = $statusMap[$product->status] ?? ['label' => $product->status ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusInfo['class'] }}">
                                {{ $statusInfo['label'] }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.product.show', $product) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('soho.product.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.product.destroy', $product) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </div>
</div>
@endsection
