@extends('soho::layouts.app')

@section('title', '库存周转')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">统计 SKU 数</div><div class="fs-4 fw-semibold">{{ $stats['skus'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">近 {{ $days }} 天出库量</div><div class="fs-4 fw-semibold">{{ number_format($stats['out_qty']) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">当前库存量</div><div class="fs-4 fw-semibold">{{ number_format($stats['stock_qty']) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">无出库 SKU</div><div class="fs-4 fw-semibold text-warning">{{ $stats['slow'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库存周转分析</h5>
        <form method="GET" class="d-flex gap-2">
            <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([7, 30, 90] as $d)
                    <option value="{{ $d }}" @selected($days === $d)>近 {{ $d }} 天</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            周转次数 = 期间出库量 ÷ 当前库存量；预计清空天数 = 当前库存量 ÷ 日均出库量。
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th class="text-end">出库量</th>
                        <th class="text-end">当前库存</th>
                        <th class="text-end">可用库存</th>
                        <th class="text-end">周转次数</th>
                        <th class="text-center">预计清空</th>
                        <th class="text-center">评级</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $level = $row->turnover >= 1 ? ['快销', 'success'] : ($row->turnover > 0 ? ['正常', 'info'] : ['滞销', 'warning']);
                        @endphp
                        <tr>
                            <td>#{{ $row->sku_id }}</td>
                            <td class="text-end">{{ number_format($row->out_qty) }}</td>
                            <td class="text-end">{{ number_format((float) $row->stock_qty) }}</td>
                            <td class="text-end">{{ number_format((float) $row->available_qty) }}</td>
                            <td class="text-end fw-semibold">{{ $row->turnover }}</td>
                            <td class="text-center small text-muted">{{ $row->turnover_days !== null ? $row->turnover_days.' 天' : '—' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $level[1] }}">{{ $level[0] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无周转数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
