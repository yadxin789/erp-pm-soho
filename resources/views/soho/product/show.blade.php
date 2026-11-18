@extends('soho::layouts.app')

@section('title', '商品详情')

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">商品详情</h5>
        <div>
            <a href="{{ route('soho.product.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-edit me-1"></i> 编辑
            </a>
            <form method="POST" action="{{ route('soho.product.destroy', $product) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fa fa-trash me-1"></i> 删除
                </button>
            </form>
            <a href="{{ route('soho.product.index') }}" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> 返回
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th width="120">商品名称</th><td>{{ $product->name }}</td></tr>
                    <tr><th>商品编码</th><td>{{ $product->product_code }}</td></tr>
                    <tr><th>商品副标题</th><td>{{ $product->subtitle ?? '-' }}</td></tr>
                    <tr><th>品牌</th><td>{{ $product->brand->name ?? '-' }}</td></tr>
                    <tr><th>分类</th><td>{{ $product->category->name ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th width="120">成本价</th><td>¥{{ number_format($product->cost_price ?? 0, 2) }}</td></tr>
                    <tr><th>零售价</th><td>¥{{ number_format($product->retail_price ?? 0, 2) }}</td></tr>
                    <tr><th>状态</th><td>
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
                    </td></tr>
                    <tr><th>SKU 数量</th><td><span class="badge bg-info">{{ $product->skus->count() }}</span></td></tr>
                    <tr><th>更新时间</th><td>{{ $product->updated_at?->format('Y-m-d H:i:s') }}</td></tr>
                </table>
            </div>
        </div>
        @if($product->description)
        <div class="mt-3">
            <h6>商品描述</h6>
            <p class="text-muted">{{ $product->description }}</p>
        </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">SKU 列表</h5>
        <a href="{{ route('soho.product.edit', $product) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-plus me-1"></i> 管理 SKU
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>SKU编码</th>
                        <th>SKU名称</th>
                        <th>规格</th>
                        <th>成本价</th>
                        <th>零售价</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->skus as $sku)
                    <tr>
                        <td>{{ $sku->id }}</td>
                        <td>{{ $sku->sku_code }}</td>
                        <td>{{ $sku->name ?? '-' }}</td>
                        <td>
                            @if(is_array($sku->specification))
                                @foreach($sku->specification as $key => $value)
                                    <span class="badge bg-light text-dark">{{ $key }}: {{ $value }}</span>
                                @endforeach
                            @else
                                {{ $sku->specification ?? '-' }}
                            @endif
                        </td>
                        <td>¥{{ number_format($sku->cost_price ?? 0, 2) }}</td>
                        <td>¥{{ number_format($sku->sale_price ?? 0, 2) }}</td>
                        <td>
                            @php
                                $skuStatusMap = [
                                    'active' => ['label' => '启用', 'class' => 'success'],
                                    'inactive' => ['label' => '停用', 'class' => 'secondary'],
                                ];
                                $skuStatusInfo = $skuStatusMap[$sku->status] ?? ['label' => $sku->status ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $skuStatusInfo['class'] }}">
                                {{ $skuStatusInfo['label'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">暂无 SKU 数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection