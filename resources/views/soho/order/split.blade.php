@extends('soho::layouts.app')

@section('title', '订单拆分')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">订单拆分</h5>
        <a href="{{ route('soho.order.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回订单
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索系统单号 / 平台单号 / 买家" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.order.split') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        @forelse($orders as $order)
            <div class="border rounded-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 bg-light rounded-top">
                    <div>
                        <span class="font-monospace fw-semibold">{{ $order->system_order_no }}</span>
                        <span class="text-muted small ms-2">平台单号 {{ $order->platform_order_no }}</span>
                        <span class="text-muted small ms-2">买家 {{ $order->buyer_nick ?: '-' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">{{ $order->items_count }} 条明细</span>
                        <span class="badge bg-light text-dark border">¥{{ number_format((float) $order->total_amount, 2) }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('soho.order.split.apply') }}" class="p-3">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-2">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>商品</th>
                                    <th>SKU</th>
                                    <th class="text-center">数量</th>
                                    <th class="text-end">单价</th>
                                    <th class="text-end">小计</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="item_ids[]" value="{{ $item->id }}">
                                        </td>
                                        <td>{{ $item->product_name ?: '-' }}</td>
                                        <td class="text-muted">{{ $item->sku_name ?: '-' }}</td>
                                        <td class="text-center">{{ (int) $item->quantity }}</td>
                                        <td class="text-end">¥{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="text-end">¥{{ number_format((float) $item->unit_price * (int) $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">勾选要拆出到新订单的明细行（至少保留一条在原订单）</div>
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('确认把勾选的明细拆分成新订单？')">
                            <i class="fa fa-scissors me-1"></i> 执行拆分
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fa fa-inbox fs-3 d-block mb-2"></i>
                没有可拆分的订单（仅待处理 / 已付款状态、且包含明细的订单可拆分）。
            </div>
        @endforelse

        {{ $orders->links() }}
    </div>
</div>
@endsection
