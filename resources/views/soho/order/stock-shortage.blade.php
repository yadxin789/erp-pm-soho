@extends('soho::layouts.app')

@section('title', '缺货订单')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">缺货明细行</div>
                <div class="fs-4 fw-semibold text-danger">{{ $stats['items'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">涉及订单</div>
                <div class="fs-4 fw-semibold">{{ $stats['orders'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">缺口总量</div>
                <div class="fs-4 fw-semibold text-warning">{{ $stats['quantity'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">缺货提醒（可用库存低于需求数量）</h5>
        <a href="{{ route('soho.order.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回订单
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索订单号 / 商品 / SKU" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.order.stock-shortage') }}" class="btn btn-outline-secondary">重置</a>
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
                        <th>商品</th>
                        <th>SKU</th>
                        <th class="text-center">需求量</th>
                        <th class="text-center">可用库存</th>
                        <th class="text-center">缺口</th>
                        <th class="text-center">订单状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-monospace small">{{ $row->system_order_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->platform_order_no }}</td>
                            <td>{{ $row->buyer_nick ?: '-' }}</td>
                            <td>{{ $row->product_name ?: '-' }}</td>
                            <td class="text-muted">{{ $row->sku_name ?: '-' }}</td>
                            <td class="text-center">{{ (int) $row->quantity }}</td>
                            <td class="text-center {{ $row->available <= 0 ? 'text-danger' : 'text-warning' }}">{{ $row->available }}</td>
                            <td class="text-center fw-semibold text-danger">{{ $row->shortage }}</td>
                            <td class="text-center">
                                @php
                                    $statusMap = [
                                        'pending' => ['待处理', 'secondary'],
                                        'paid' => ['已付款', 'success'],
                                        'frozen' => ['已冻结', 'warning'],
                                        'shipped' => ['已发货', 'info'],
                                        'completed' => ['已完成', 'success'],
                                        'cancelled' => ['已取消', 'dark'],
                                    ];
                                    [$label, $class] = $statusMap[$row->order_status] ?? [$row->order_status, 'light'];
                                @endphp
                                <span class="badge bg-{{ $class }}">{{ $label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa fa-circle-check text-success me-1"></i>
                                当前没有缺货订单，库存可以满足所有待发货需求。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
