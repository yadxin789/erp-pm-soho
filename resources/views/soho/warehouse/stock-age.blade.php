@extends('soho::layouts.app')

@section('title', '库龄分析')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">批次数</div><div class="fs-4 fw-semibold">{{ $stats['batches'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">在库数量</div><div class="fs-4 fw-semibold">{{ number_format($stats['quantity']) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">库存成本</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['value'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">60 天内临期</div><div class="fs-4 fw-semibold text-danger">{{ $stats['expiring'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">库龄分布（按批次入库时间）</h5></div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($ages as $label => $count)
                @php $total = array_sum($ages); $pct = $total > 0 ? round($count / $total * 100, 1) : 0; @endphp
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">{{ $label }} 天</div>
                        <div class="fs-5 fw-semibold">{{ $count }} 批</div>
                        <div class="progress mt-2" style="height:8px">
                            <div class="progress-bar {{ $label === '180+' ? 'bg-danger' : ($label === '91-180' ? 'bg-warning' : 'bg-success') }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="small text-muted mt-1">{{ $pct }}%</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">批次明细</h5>
        <a href="{{ route('soho.warehouse.inventory.turnover') }}" class="btn btn-outline-secondary btn-sm">库存周转</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.warehouse.stock.age') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>批次号</th>
                        <th>SKU</th>
                        <th>仓库</th>
                        <th class="text-end">数量</th>
                        <th class="text-end">可用</th>
                        <th class="text-end">成本</th>
                        <th class="text-center">入库日期</th>
                        <th class="text-center">库龄</th>
                        <th class="text-center">效期</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $ageDays = $row->created_at ? now()->diffInDays($row->created_at) : 0;
                            $expireDays = $row->expiry_date ? now()->diffInDays($row->expiry_date, false) : null;
                        @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->batch_no }}</td>
                            <td>#{{ $row->sku_id }}</td>
                            <td>{{ $warehouses[$row->warehouse_id]->name ?? '仓库#'.$row->warehouse_id }}</td>
                            <td class="text-end">{{ number_format((float) $row->quantity) }}</td>
                            <td class="text-end">{{ number_format((float) $row->available_quantity) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->total_cost, 2) }}</td>
                            <td class="text-center small text-muted">{{ $row->created_at ? substr($row->created_at, 0, 10) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $ageDays > 180 ? 'danger' : ($ageDays > 90 ? 'warning' : 'success') }}">{{ $ageDays }} 天</span>
                            </td>
                            <td class="text-center small {{ $expireDays !== null && $expireDays <= 60 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $row->expiry_date ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">暂无批次库存。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
