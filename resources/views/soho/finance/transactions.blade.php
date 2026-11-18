@extends('soho::layouts.app')

@section('title', '财务交易流水')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">财务交易流水</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">交易流水列表</h5>
        <a href="{{ route('soho.finance.transactions') }}" class="btn btn-outline-primary">
            <i class="fa fa-refresh"></i> 刷新
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.finance.transactions') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索交易号/订单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="income" @selected(request('type') == 'income')>收入</option>
                        <option value="expense" @selected(request('type') == 'expense')>支出</option>
                        <option value="refund" @selected(request('type') == 'refund')>退款</option>
                        <option value="settle" @selected(request('type') == 'settle')>结算</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.finance.transactions') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>交易号</th>
                        <th>类型</th>
                        <th>关联订单</th>
                        <th>金额</th>
                        <th>币种</th>
                        <th>交易方式</th>
                        <th>状态</th>
                        <th>交易时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_no }}</td>
                        <td>
                            @if($transaction->type == 'income')
                                <span class="badge bg-success">收入</span>
                            @elseif($transaction->type == 'expense')
                                <span class="badge bg-danger">支出</span>
                            @elseif($transaction->type == 'refund')
                                <span class="badge bg-warning text-dark">退款</span>
                            @else
                                <span class="badge bg-info">{{ $transaction->type }}</span>
                            @endif
                        </td>
                        <td>{{ $transaction->order_no ?? '-' }}</td>
                        <td class="text-{{ $transaction->type == 'income' ? 'success' : 'danger' }} fw-bold">
                            {{ $transaction->type == 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td>{{ $transaction->currency ?? 'CNY' }}</td>
                        <td>{{ $transaction->payment_method ?? '-' }}</td>
                        <td>
                            @if($transaction->status == 'completed')
                                <span class="badge bg-success">已完成</span>
                            @elseif($transaction->status == 'pending')
                                <span class="badge bg-warning text-dark">处理中</span>
                            @else
                                <span class="badge bg-danger">失败</span>
                            @endif
                        </td>
                        <td>{{ $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links() }}
    </div>
</div>
@endsection