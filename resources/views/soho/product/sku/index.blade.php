@extends('soho::layouts.app')

@section('title', 'SKU 管理')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">SKU 总数</div>
                <div class="fs-4 fw-semibold">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">启用中</div>
                <div class="fs-4 fw-semibold text-success">{{ $stats['active'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">已停用</div>
                <div class="fs-4 fw-semibold text-secondary">{{ $stats['inactive'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">SKU 列表</h5>
        <a href="{{ route('soho.product.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回商品
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索 SKU 编码 / 条码 / 名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.sku') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>SKU 编码</th>
                        <th>名称</th>
                        <th>规格</th>
                        <th>条码</th>
                        <th class="text-end">成本价</th>
                        <th class="text-end">销售价</th>
                        <th class="text-center">安全库存</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skus as $sku)
                        <tr>
                            <td>{{ $sku->id }}</td>
                            <td class="font-monospace">{{ $sku->sku_code }}</td>
                            <td>{{ $sku->name }}</td>
                            <td class="text-muted">{{ $sku->specification ?: '-' }}</td>
                            <td class="font-monospace small">{{ $sku->barcode ?: '-' }}</td>
                            <td class="text-end">¥{{ number_format((float) $sku->cost_price, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $sku->sale_price, 2) }}</td>
                            <td class="text-center">{{ (int) $sku->safety_quantity }}</td>
                            <td class="text-center">
                                @php
                                    $statusMap = [
                                        'active' => ['启用', 'success'],
                                        'inactive' => ['停用', 'secondary'],
                                        'draft' => ['草稿', 'warning'],
                                    ];
                                    [$label, $class] = $statusMap[$sku->status] ?? [$sku->status ?: '未知', 'light'];
                                @endphp
                                <span class="badge bg-{{ $class }}">{{ $label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">暂无 SKU 数据</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $skus->links() }}
    </div>
</div>
@endsection
