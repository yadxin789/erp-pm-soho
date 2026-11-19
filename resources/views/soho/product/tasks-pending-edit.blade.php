@extends('soho::layouts.app')

@section('title', '待修改商品')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">草稿商品</div><div class="fs-4 fw-semibold">{{ $stats['draft'] }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审核商品</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">缺 SKU 商品</div><div class="fs-4 fw-semibold text-danger">{{ $stats['no_sku'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待修改商品</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('soho.product.tasks.returned') }}" class="btn btn-outline-secondary btn-sm">退回商品</a>
            <a href="{{ route('soho.product.tasks.exception') }}" class="btn btn-outline-secondary btn-sm">资料异常</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="draft" @selected(request('status') === 'draft')>草稿</option>
                        <option value="pending" @selected(request('status') === 'pending')>待审核</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="商品名称 / 编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.tasks.pending-edit') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品编码</th>
                        <th>商品名称</th>
                        <th class="text-end">成本价</th>
                        <th class="text-end">零售价</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">更新于</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['draft' => ['草稿', 'secondary'], 'pending' => ['待审核', 'warning']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->product_code }}</td>
                            <td>{{ $row->name }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->cost_price, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->retail_price, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->updated_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">没有待修改的商品。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
