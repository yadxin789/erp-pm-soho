@extends('soho::layouts.app')

@section('title', '待收货退货')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">退货包裹总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">在途 / 已发出</div><div class="fs-4 fw-semibold text-warning">{{ $stats['in_transit'] }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已收货</div><div class="fs-4 fw-semibold text-success">{{ $stats['received'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">退货收货</h5>
        <a href="{{ route('soho.warehouse.return.inspect') }}" class="btn btn-outline-secondary btn-sm">退货质检</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['shipped' => '已发出', 'in_transit' => '运输中', 'received' => '已收货'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="退货单号 / 运单号 / 收件人" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.warehouse.return.receive') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>退货单号</th>
                        <th>运单号</th>
                        <th>承运商</th>
                        <th>收件人</th>
                        <th>仓库</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">收货时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['shipped' => ['已发出', 'info'], 'in_transit' => ['运输中', 'warning'], 'received' => ['已收货', 'success']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->return_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->tracking_no ?: '-' }}</td>
                            <td class="small">{{ $row->carrier_code ?: '-' }}</td>
                            <td class="small">{{ $row->receiver_name ?: '-' }} {{ $row->receiver_phone ? '· '.$row->receiver_phone : '' }}</td>
                            <td>{{ $warehouses[$row->warehouse_id]->name ?? '仓库#'.$row->warehouse_id }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->received_at ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无退货包裹。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
