@extends('soho::layouts.app')

@section('title', '库存预警')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">预警规则数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">危险</div><div class="fs-4 fw-semibold text-danger">{{ $stats['danger'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">警告</div><div class="fs-4 fw-semibold text-warning">{{ $stats['warning'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">低于安全库存</div><div class="fs-4 fw-semibold">{{ $stats['short'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">安全库存预警</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="level" class="form-select">
                        <option value="">全部级别</option>
                        <option value="danger" @selected($level === 'danger')>危险</option>
                        <option value="warning" @selected($level === 'warning')>警告</option>
                        <option value="normal" @selected($level === 'normal')>正常</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="SKU 编码 / 商品名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2 form-check ms-2">
                    <input type="checkbox" class="form-check-input" id="only_short" name="only_short" value="1" @checked(request('only_short') === '1')>
                    <label class="form-check-label small" for="only_short">只看低于安全库存</label>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.warehouse.stock-warning') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>商品</th>
                        <th>仓库</th>
                        <th class="text-end">当前库存</th>
                        <th class="text-end">安全库存</th>
                        <th class="text-end">缺口</th>
                        <th class="text-center">级别</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $gap = (float) $row->safe_stock - (float) $row->current_stock;
                            $badge = ['danger' => 'danger', 'warning' => 'warning', 'normal' => 'success'][$row->alert_level] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->sku_code }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->warehouse_name ?: '-' }}</td>
                            <td class="text-end">{{ number_format((float) $row->current_stock) }}</td>
                            <td class="text-end">{{ number_format((float) $row->safe_stock) }}</td>
                            <td class="text-end {{ $gap > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $gap > 0 ? number_format($gap) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $badge }}">{{ ['danger' => '危险', 'warning' => '警告', 'normal' => '正常'][$row->alert_level] ?? $row->alert_level }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无预警数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
