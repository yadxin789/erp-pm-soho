@extends('soho::layouts.app')

@section('title', '待履约')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待履约订单</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('soho.order.fulfillment') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-clipboard-check me-1"></i> 履约单
            </a>
            <a href="{{ route('soho.order.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> 返回订单
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted mb-3">
            已付款且未发货的订单在这里生成履约单；生成后订单明细会被补齐仓库，便于后续分仓拣货。
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索系统单号 / 平台单号 / 买家" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.order.pending-fulfill') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>系统单号</th>
                        <th>平台单号</th>
                        <th>买家</th>
                        <th class="text-center">明细数</th>
                        <th class="text-center">未分仓明细</th>
                        <th class="text-end">金额</th>
                        <th class="text-center">下单时间</th>
                        <th class="text-center">履约状态</th>
                        <th style="width: 260px;">生成履约单</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="font-monospace small">{{ $order->system_order_no }}</td>
                            <td class="font-monospace small text-muted">{{ $order->platform_order_no }}</td>
                            <td>{{ $order->buyer_nick ?: '-' }}</td>
                            <td class="text-center">{{ $order->items_count }}</td>
                            <td class="text-center">
                                @php $missing = (int) ($noWarehouse[$order->id] ?? 0); @endphp
                                @if($missing > 0)
                                    <span class="badge bg-warning text-dark">{{ $missing }}</span>
                                @else
                                    <span class="text-success"><i class="fa fa-check"></i></span>
                                @endif
                            </td>
                            <td class="text-end">¥{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td class="text-center small text-muted">{{ $order->order_created_at }}</td>
                            <td class="text-center">
                                @if(isset($created[$order->id]))
                                    <span class="badge bg-info">已生成（{{ $created[$order->id] }}）</span>
                                @else
                                    <span class="badge bg-secondary">未生成</span>
                                @endif
                            </td>
                            <td>
                                @if(! isset($created[$order->id]))
                                    <form method="POST" action="{{ route('soho.order.pending-fulfill.store') }}" class="d-flex gap-1">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <select name="warehouse_id" class="form-select form-select-sm" required>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} {{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm text-nowrap">生成</button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa fa-circle-check text-success me-1"></i> 没有待履约的订单。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</div>
@endsection
