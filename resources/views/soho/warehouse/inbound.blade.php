@extends('soho::layouts.app')

@section('title', '采购入库')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">入库单总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待收货 / 处理中</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已完成</div><div class="fs-4 fw-semibold text-success">{{ $stats['completed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已入库数量</div><div class="fs-4 fw-semibold">{{ number_format($stats['quantity']) }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">采购到货入库</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待收货', 'processing' => '处理中', 'completed' => '已完成', 'cancelled' => '已取消'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="入库单号 / 到货单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.warehouse.inbound') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>入库单号</th>
                        <th>到货单号</th>
                        <th>仓库</th>
                        <th class="text-end">数量</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">入库时间</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['pending' => ['待收货', 'warning'], 'processing' => ['处理中', 'info'], 'completed' => ['已完成', 'success'], 'cancelled' => ['已取消', 'secondary']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->inbound_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->arrival_no ?: '-' }}</td>
                            <td>{{ $warehouses[$row->warehouse_id]->name ?? '仓库#'.$row->warehouse_id }}</td>
                            <td class="text-end">{{ number_format((float) $row->quantity) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span>
                            </td>
                            <td class="text-center small text-muted">{{ $row->inbound_at ?: '-' }}</td>
                            <td class="small text-muted">{{ $row->remark ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无入库单。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
