@extends('soho::layouts.app')

@section('title', '大额差异审批')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">涉及店铺</div><div class="fs-4 fw-semibold">{{ $stats['shops'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">存在差异店铺</div><div class="fs-4 fw-semibold text-warning">{{ $stats['diff_shops'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">差异金额合计</div><div class="fs-4 fw-semibold text-danger">¥{{ number_format($stats['diff_amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">争议结算单</div><div class="fs-4 fw-semibold">{{ $stats['disputed'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">平台结算 vs 财务结算差异</h5></div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            差异 = 平台结算金额合计 − 财务结算金额合计；|差异| > 0.01 视为需审批。
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>店铺</th>
                        <th class="text-end">平台结算</th>
                        <th class="text-end">财务结算</th>
                        <th class="text-end">差异</th>
                        <th class="text-center">结论</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($platformByShop as $row)
                        <tr>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->platform_amount, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->finance_amount, 2) }}</td>
                            <td class="text-end {{ abs($row->diff) > 0.01 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ number_format((float) $row->diff, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ abs($row->diff) > 0.01 ? 'danger' : 'success' }}">{{ abs($row->diff) > 0.01 ? '待审批' : '一致' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">暂无结算数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台结算单明细</h5>
        <a href="{{ route('soho.finance.platform.settlement') }}" class="btn btn-outline-secondary btn-sm">平台结算</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待结算', 'settled' => '已结算', 'disputed' => '有争议', 'rejected' => '已驳回'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-filter me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.diff.approval') }}" class="btn btn-outline-secondary">重置</a>
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
                        <th class="text-center">周期</th>
                        <th class="text-end">金额</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">审批动作</th>
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
                            <td class="text-center small text-muted">
                                @if($row->status === 'disputed')
                                    <span class="text-danger">待主管审批</span>
                                @elseif($row->status === 'pending')
                                    <span class="text-warning">待核对</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无结算单。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
