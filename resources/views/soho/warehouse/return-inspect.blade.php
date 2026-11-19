@extends('soho::layouts.app')

@section('title', '待质检退货')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">质检记录</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待质检</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">合格</div><div class="fs-4 fw-semibold text-success">{{ $stats['qualified'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">不合格</div><div class="fs-4 fw-semibold text-danger">{{ $stats['unqualified'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">待质检退货（已收货）</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr><th>退货单号</th><th>订单 ID</th><th class="text-center">收货时间</th><th class="text-center">操作</th></tr>
                </thead>
                <tbody>
                    @forelse($pending as $p)
                        <tr>
                            <td class="font-monospace small">{{ $p->return_no }}</td>
                            <td class="small text-muted">#{{ $p->order_id }}</td>
                            <td class="text-center small text-muted">{{ $p->received_at ?: '-' }}</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">待质检</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">没有待质检的退货包裹。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">质检记录</h5>
        <a href="{{ route('soho.warehouse.return.receive') }}" class="btn btn-outline-secondary btn-sm">退货收货</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="result" class="form-select">
                        <option value="">全部结果</option>
                        <option value="qualified" @selected(request('result') === 'qualified')>合格</option>
                        <option value="unqualified" @selected(request('result') === 'unqualified')>不合格</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.warehouse.return.inspect') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>退货单 ID</th>
                        <th>质检员</th>
                        <th class="text-center">结果</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">质检时间</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-monospace small">#{{ $row->return_order_id }}</td>
                            <td class="small">#{{ $row->inspector_id }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row->result === 'qualified' ? 'success' : ($row->result === 'unqualified' ? 'danger' : 'secondary') }}">
                                    {{ ['qualified' => '合格', 'unqualified' => '不合格'][$row->result] ?? $row->result }}
                                </span>
                            </td>
                            <td class="text-center small text-muted">{{ $row->status }}</td>
                            <td class="text-center small text-muted">{{ $row->inspected_at ?: '-' }}</td>
                            <td class="small">{{ $row->remark ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无质检记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
