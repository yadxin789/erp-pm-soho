@extends('soho::layouts.app')

@section('title', '费用分摊')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">分摊记录</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">费用金额</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已分摊金额</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['allocated'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">分摊方法数</div><div class="fs-4 fw-semibold">{{ $stats['methods'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">费用分摊明细</h5>
        <a href="{{ route('soho.finance.cost.audit') }}" class="btn btn-outline-secondary btn-sm">费用稽核</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="expense_type" class="form-select">
                        <option value="">全部费用类型</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(request('expense_type') === $type)>{{ ['advertising' => '广告费', 'logistics' => '物流费', 'platform' => '平台费', 'warehouse' => '仓储费', 'other' => '其他'][$type] ?? $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="allocation_method" class="form-select">
                        <option value="">全部分摊方法</option>
                        @foreach($methods as $m)
                            <option value="{{ $m }}" @selected(request('allocation_method') === $m)>{{ ['equal' => '平均分摊', 'percentage' => '按比例', 'weight' => '按权重', 'volume' => '按体积'][$m] ?? $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.cost.allocation') }}" class="btn btn-outline-secondary">重置</a>
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
                        <th class="text-center">分摊方法</th>
                        <th class="text-end">分摊金额</th>
                        <th class="text-center">偏差</th>
                        <th class="text-center">创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $diff = round((float) $row->allocated_amount - (float) $row->amount, 2); @endphp
                        <tr>
                            <td class="font-monospace small">#{{ $row->order_id }}</td>
                            <td class="text-center small">{{ ['advertising' => '广告费', 'logistics' => '物流费', 'platform' => '平台费', 'warehouse' => '仓储费', 'other' => '其他'][$row->expense_type] ?? $row->expense_type }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-center small">{{ ['equal' => '平均分摊', 'percentage' => '按比例', 'weight' => '按权重', 'volume' => '按体积'][$row->allocation_method] ?? $row->allocation_method }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->allocated_amount, 2) }}</td>
                            <td class="text-center {{ abs($diff) > 0.01 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ abs($diff) > 0.01 ? number_format($diff, 2) : '一致' }}</td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无分摊记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
