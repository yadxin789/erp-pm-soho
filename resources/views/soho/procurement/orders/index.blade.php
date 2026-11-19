@extends('soho::layouts.app')

@section('title', '采购订单')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">采购订单</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">采购订单</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 搜索/筛选 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small">订单编号</label>
                        <input type="text" class="form-control" name="order_no" value="{{ request('order_no') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">供应商</label>
                        <input type="text" class="form-control" name="supplier" value="{{ request('supplier') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">状态</label>
                        <select class="form-select" name="status">
                            <option value="">全部</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">下单日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">至</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.orders.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body py-3"><small>总订单数</small><h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body py-3"><small>运输中</small><h3 class="mb-0">{{ $stats['shipping'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body py-3"><small>已完成</small><h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white"><div class="card-body py-3"><small>已取消</small><h3 class="mb-0">{{ $stats['cancelled'] ?? 0 }}</h3></div></div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>订单编号</th>
                        <th>供应商</th>
                        <th>商品数</th>
                        <th>订单金额</th>
                        <th>状态</th>
                        <th>下单日期</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="text-muted">{{ $order->id }}</td>
                            <td><code>{{ $order->order_no ?? 'PO-' . $order->id }}</code></td>
                            <td>{{ $order->supplier_name ?? '-' }}</td>
                            <td>{{ $order->items_count ?? 0 }} 项</td>
                            <td>¥{{ number_format($order->total_amount ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'secondary', 'confirmed' => 'warning', 'shipping' => 'info', 'arrived' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->total() > 0)
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
