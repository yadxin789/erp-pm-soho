@extends('soho::layouts.app')

@section('title', '结算审核')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">结算单总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审核</div><div class="fs-4 fw-semibold text-warning">{{ $stats['waiting'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已完成</div><div class="fs-4 fw-semibold text-success">{{ $stats['completed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">失败</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">结算审核流程</h5></div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            审核顺序：待结算 → 处理中 → 已完成；失败与已取消单据列为异常，需财务主管复核。
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待结算', 'processing' => '处理中', 'completed' => '已完成', 'failed' => '失败', 'cancelled' => '已取消'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="结算单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.finance.settlement.audit') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>结算单号</th>
                        <th>店铺</th>
                        <th class="text-end">金额</th>
                        <th class="text-center">结算方式</th>
                        <th class="text-center">当前状态</th>
                        <th class="text-center">审核环节</th>
                        <th class="text-center">创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['pending' => ['待结算', 'warning'], 'processing' => ['处理中', 'info'], 'completed' => ['已完成', 'success'], 'failed' => ['失败', 'danger'], 'cancelled' => ['已取消', 'secondary']];
                            $steps = [
                                ['财务初审', true],
                                ['主管复核', in_array($row->status, ['processing', 'completed', 'failed'], true)],
                                ['打款完成', $row->status === 'completed'],
                            ];
                        @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->settle_no }}</td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-center small">{{ ['bank' => '银行转账', 'alipay' => '支付宝', 'wechat' => '微信'][$row->settle_method] ?? $row->settle_method }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    @foreach($steps as $step)
                                        <span class="badge {{ $step[1] ? 'bg-success' : 'bg-light text-muted border' }}">{{ $step[0] }}</span>
                                        @if(! $loop->last)<i class="fa fa-angle-right text-muted"></i>@endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无待审核结算单。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
