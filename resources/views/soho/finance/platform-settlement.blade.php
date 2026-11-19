@extends('soho::layouts.app')

@section('title', '平台结算')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">结算单数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">结算金额合计</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已结算</div><div class="fs-4 fw-semibold text-success">{{ $stats['settled'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">争议 / 驳回</div><div class="fs-4 fw-semibold text-danger">{{ $stats['disputed'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台结算单</h5>
        <a href="{{ route('soho.finance.diff.approval') }}" class="btn btn-outline-secondary btn-sm">差异审批</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" @selected(request('platform') === $p)>{{ strtoupper($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待结算', 'settled' => '已结算', 'disputed' => '有争议', 'rejected' => '已驳回'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="结算单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.platform.settlement') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>结算单号</th>
                        <th class="text-center">平台</th>
                        <th>店铺</th>
                        <th class="text-center">结算周期</th>
                        <th class="text-end">金额</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['pending' => ['待结算', 'warning'], 'settled' => ['已结算', 'success'], 'disputed' => ['有争议', 'danger'], 'rejected' => ['已驳回', 'secondary']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->settlement_no }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform) }}</span></td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-center small text-muted">{{ $row->period_start }} ~ {{ $row->period_end }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无平台结算单。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
