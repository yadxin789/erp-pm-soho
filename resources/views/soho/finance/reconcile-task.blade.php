@extends('soho::layouts.app')

@section('title', '对账任务')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">任务总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待对账</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已确认</div><div class="fs-4 fw-semibold text-success">{{ $stats['confirmed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">对账金额合计</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">对账任务</h5>
        <a href="{{ route('soho.finance.diff.approval') }}" class="btn btn-outline-secondary btn-sm">差异审批</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待对账', 'confirmed' => '已确认', 'completed' => '已完成', 'cancelled' => '已取消'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="对账单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.reconcile.task') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>对账单号</th>
                        <th>店铺</th>
                        <th class="text-center">对账期间</th>
                        <th class="text-end">金额</th>
                        <th class="text-center">状态</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['pending' => ['待对账', 'warning'], 'confirmed' => ['已确认', 'info'], 'completed' => ['已完成', 'success'], 'cancelled' => ['已取消', 'secondary']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->reconcile_no }}</td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-center small text-muted">{{ $row->period_start }} ~ {{ $row->period_end }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="small text-muted">{{ $row->remark ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无对账任务。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
