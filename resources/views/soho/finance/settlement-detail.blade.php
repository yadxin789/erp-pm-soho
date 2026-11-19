@extends('soho::layouts.app')

@section('title', '结算明细')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">结算单数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">金额合计</div><div class="fs-4 fw-semibold">¥{{ number_format($stats['amount'], 2) }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待处理</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已完成</div><div class="fs-4 fw-semibold text-success">{{ $stats['completed'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">按店铺汇总</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>店铺</th>
                        <th class="text-center">结算单数</th>
                        <th class="text-end">金额合计</th>
                        <th class="text-end">已完成金额</th>
                        <th class="text-end">未完成金额</th>
                        <th style="min-width:140px">完成率</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byShop as $row)
                        @php
                            $rate = (float) $row->amount_total > 0 ? round((float) $row->completed_amount / (float) $row->amount_total * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-center">{{ $row->settle_count }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount_total, 2) }}</td>
                            <td class="text-end text-success">¥{{ number_format((float) $row->completed_amount, 2) }}</td>
                            <td class="text-end text-warning">¥{{ number_format((float) $row->amount_total - (float) $row->completed_amount, 2) }}</td>
                            <td>
                                <div class="progress" style="height:16px">
                                    <div class="progress-bar bg-success" style="width: {{ min(100, $rate) }}%">{{ $rate }}%</div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无结算数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">结算单明细</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $s)
                            <option value="{{ $s->id }}" @selected(request('shop_id') == $s->id)>{{ $s->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
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
                    <a href="{{ route('soho.finance.settlement.detail') }}" class="btn btn-outline-secondary">重置</a>
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
                        <th class="text-center">状态</th>
                        <th class="text-center">创建时间</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['pending' => ['待结算', 'warning'], 'processing' => ['处理中', 'info'], 'completed' => ['已完成', 'success'], 'failed' => ['失败', 'danger'], 'cancelled' => ['已取消', 'secondary']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->settle_no }}</td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->amount, 2) }}</td>
                            <td class="text-center small">{{ ['bank' => '银行转账', 'alipay' => '支付宝', 'wechat' => '微信'][$row->settle_method] ?? $row->settle_method }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                            <td class="small text-muted">{{ $row->remark ?: '-' }}</td>
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
