@extends('soho::layouts.app')

@section('title', '库区管理')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">库区数</div><div class="fs-4 fw-semibold">{{ $stats['zones'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">库位数</div><div class="fs-4 fw-semibold">{{ $stats['locations'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">容量合计</div><div class="fs-4 fw-semibold">{{ number_format($stats['capacity']) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">占用合计</div><div class="fs-4 fw-semibold">{{ number_format($stats['usage']) }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库区一览</h5>
        <a href="{{ route('soho.warehouse.locations') }}" class="btn btn-outline-secondary btn-sm">库位明细</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>仓库</th>
                        <th>库区</th>
                        <th class="text-center">库位数</th>
                        <th class="text-center">启用</th>
                        <th class="text-end">容量</th>
                        <th class="text-end">占用</th>
                        <th>使用率</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $rate = $row->capacity_total > 0 ? round($row->usage_total / $row->capacity_total * 100, 1) : 0; @endphp
                        <tr>
                            <td>{{ $warehouses[$row->warehouse_id]->name ?? '仓库#'.$row->warehouse_id }}</td>
                            <td class="fw-semibold">{{ $row->zone_name ?: '未分区' }}</td>
                            <td class="text-center">{{ $row->location_count }}</td>
                            <td class="text-center"><span class="badge bg-success">{{ $row->active_count }}</span></td>
                            <td class="text-end">{{ number_format($row->capacity_total) }}</td>
                            <td class="text-end">{{ number_format($row->usage_total) }}</td>
                            <td style="min-width:160px">
                                <div class="progress" style="height:16px">
                                    <div class="progress-bar {{ $rate >= 90 ? 'bg-danger' : ($rate >= 70 ? 'bg-warning' : 'bg-success') }}" style="width: {{ min(100, $rate) }}%">{{ $rate }}%</div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无库区数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
