@extends('soho::layouts.app')
@section('title', '待处理售后')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">待处理售后</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="售后单号/买家昵称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="refund" @selected(request('type') == 'refund')>仅退款</option>
                        <option value="return" @selected(request('type') == 'return')>退货退款</option>
                        <option value="exchange" @selected(request('type') == 'exchange')>换货</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.aftersale.pending') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>售后单号</th>
                        <th>订单号</th>
                        <th>类型</th>
                        <th>原因</th>
                        <th>申请金额</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aftersales as $item)
                    <tr>
                        <td>{{ $item->after_sale_no }}</td>
                        <td>{{ $item->platformOrder->platform_order_no ?? '-' }}</td>
                        <td>
                            @if($item->type == 'refund')<span class="badge bg-info">仅退款</span>
                            @elseif($item->type == 'return')<span class="badge bg-warning">退货退款</span>
                            @elseif($item->type == 'exchange')<span class="badge bg-primary">换货</span>
                            @else<span class="badge bg-secondary">{{ $item->type }}</span>@endif
                        </td>
                        <td>{{ $item->reason ?? '-' }}</td>
                        <td>¥{{ number_format($item->refund_amount, 2) }}</td>
                        <td><span class="badge bg-warning">待处理</span></td>
                        <td>{{ $item->apply_time?->format('Y-m-d H:i') ?? $item->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('soho.aftersale.handle', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('确定批准此售后申请？')">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-sm btn-success">批准</button>
                            </form>
                            <form action="{{ route('soho.aftersale.handle', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('确定拒绝此售后申请？')">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-sm btn-danger">拒绝</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无待处理售后</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $aftersales->links() }}
    </div>
</div>
@endsection
