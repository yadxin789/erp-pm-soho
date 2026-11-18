@extends('soho::layouts.app')
@section('title', '已完成订单')
@section('content')
<div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.order.index') }}">订单管理</a></li>
            <li class="breadcrumb-item active">已完成订单</li>
        </ol>
    </nav>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">已完成订单列表</h5>
        <span class="badge bg-success">{{ $orders->total() }} 条记录</span>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="订单号/买家昵称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach(\App\Soho\Models\Shop::all() as $shop)
                        <option value="{{ $shop->id }}" @selected(request('shop_id') == $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.order.completed') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>订单编号</th>
                        <th>店铺</th>
                        <th>买家</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th>完成时间</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->shop->name ?? '-' }}</td>
                        <td>{{ $order->buyer_nickname ?? '-' }}</td>
                        <td>¥{{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-success">已完成</span>
                        </td>
                        <td>{{ $order->completed_time?->format('Y-m-d H:i:s') ?? '-' }}</td>
                        <td>{{ $order->order_created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('soho.order.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">暂无数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
