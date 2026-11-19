@extends('soho::layouts.app')

@section('title', '费用分摊审核')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">分摊记录</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">存在偏差</div><div class="fs-4 fw-semibold text-danger">{{ $stats['diff'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">费用金额</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">分摊金额</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['allocated'], 2) }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">按费用类型汇总</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>费用类型</th><th class="text-center">记录数</th><th class="text-end">费用金额</th><th class="text-end">分摊金额</th><th class="text-end">偏差</th></tr></thead>
                <tbody>
                    @forelse($byType as $row)
                        @php $diff = round((float) $row->allocated_total - (float) $row->amount_total, 2); @endphp
                        <tr>
                            <td>{{ ['advertising' => '广告费', 'logistics' => '物流费', 'platform' => '平台费', 'warehouse' => '仓储费', 'other' => '其他'][$row->expense_type] ?? $row->expense_type }}</td>
                            <td class="text-center">{{ $row->cnt }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount_total, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->allocated_total, 2) }}</td>
                            <td class="text-end {{ abs($diff) > 0.01 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ abs($diff) > 0.01 ? number_format($diff, 2) : '一致' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">暂无数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">稽核明细（按偏差倒序）</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="expense_type" class="form-select">
                        <option value="">全部费用类型</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(request('expense_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-check ms-2">
                    <input type="checkbox" class="form-check-input" id="only_diff" name="only_diff" value="1" @checked(request('only_diff') === '1')>
                    <label class="form-check-label small" for="only_diff">只看存在偏差</label>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-filter me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.cost.audit') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>订单 ID</th>
                        <th class="text-center">费用类型</th>
                        <th class="text-end">费用金额</th>
                        <th class="text-end">分摊金额</th>
                        <th class="text-end">偏差</th>
                        <th class="text-center">分摊方法</th>
                        <th class="text-center">结论</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $diff = round((float) $row->allocated_amount - (float) $row->amount, 2); @endphp
                        <tr>
                            <td class="font-monospace small">#{{ $row->order_id }}</td>
                            <td class="text-center small">{{ $row->expense_type }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->allocated_amount, 2) }}</td>
                            <td class="text-end {{ abs($diff) > 0.01 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ number_format($diff, 2) }}</td>
                            <td class="text-center small">{{ $row->allocation_method }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ abs($diff) > 0.01 ? 'danger' : 'success' }}">{{ abs($diff) > 0.01 ? '需复核' : '通过' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无稽核记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
