@extends('soho::layouts.app')

@section('title', '商品资料异常')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">异常合计</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">缺少 SKU</div><div class="fs-4 fw-semibold text-danger">{{ $stats['no_sku'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">缺少商品图</div><div class="fs-4 fw-semibold text-warning">{{ $stats['no_image'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">价格异常</div><div class="fs-4 fw-semibold">{{ $stats['bad_price'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">资料异常清单</h5>
        <a href="{{ route('soho.product.spu.detail') }}" class="btn btn-outline-secondary btn-sm">SPU 明细</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="商品名称 / 编码" value="{{ $keyword }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.tasks.exception') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品编码</th>
                        <th>商品名称</th>
                        <th class="text-center">当前状态</th>
                        <th class="text-center">异常类型</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['draft' => ['草稿', 'secondary'], 'pending' => ['待审核', 'warning'], 'active' => ['在售', 'success']];
                        @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->product_code }}</td>
                            <td>{{ $row->name }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-{{ str_contains($row->issue, 'SKU') ? 'danger' : (str_contains($row->issue, '价格') ? 'warning' : 'secondary') }}">{{ $row->issue }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">
                            <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                            商品资料完整，无异常。
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
