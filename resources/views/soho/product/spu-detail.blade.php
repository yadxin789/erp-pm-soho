@extends('soho::layouts.app')

@section('title', 'SPU 明细')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">SPU 数</div><div class="fs-4 fw-semibold">{{ $stats['products'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">SKU 数</div><div class="fs-4 fw-semibold">{{ $stats['skus'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">在售 SPU</div><div class="fs-4 fw-semibold text-success">{{ $stats['active'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">无 SKU 商品</div><div class="fs-4 fw-semibold text-danger">{{ $stats['no_sku'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">SPU 明细（含 SKU 汇总）</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['active' => '在售', 'pending' => '待审核', 'draft' => '草稿'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">全部分类</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="SPU 名称 / 编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.spu.detail') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>SPU 编码</th>
                        <th>名称</th>
                        <th>分类</th>
                        <th class="text-center">SKU 数</th>
                        <th class="text-end">成本价</th>
                        <th class="text-end">零售价</th>
                        <th class="text-end">SKU 价格区间</th>
                        <th class="text-end">安全库存合计</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['draft' => ['草稿', 'secondary'], 'pending' => ['待审核', 'warning'], 'active' => ['在售', 'success']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->product_code }}</td>
                            <td>{{ $row->name }}</td>
                            <td class="small">{{ $categories[$row->category_id]->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row->sku_count > 0 ? 'info' : 'danger' }}">{{ $row->sku_count }}</span>
                            </td>
                            <td class="text-end">¥{{ number_format((float) $row->cost_price, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->retail_price, 2) }}</td>
                            <td class="text-end small">
                                @if($row->sku_count > 0)
                                    ¥{{ number_format((float) $row->min_price, 2) }} ~ ¥{{ number_format((float) $row->max_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">{{ number_format((float) $row->safety_total) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">暂无 SPU 数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
