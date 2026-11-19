@extends('soho::layouts.app')

@section('title', '订单合并')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">订单合并</h5>
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
                    <a href="{{ route('soho.order.merge') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="alert alert-light border small text-muted mb-3">
            合并规则：只能合并<b>同一买家</b>、状态为<b>待处理 / 已付款</b>的订单；明细行会全部并入目标订单，被合并订单标记为「已取消」并写入备注。
        </div>

        <form method="POST" action="{{ route('soho.order.merge.apply') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 56px;" class="text-center">目标</th>
                            <th style="width: 40px;" class="text-center">合并</th>
                            <th>系统单号</th>
                            <th>平台单号</th>
                            <th>买家</th>
                            <th class="text-center">明细数</th>
                            <th class="text-end">金额</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">该买家在途单</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="text-center">
                                    <input class="form-check-input" type="radio" name="target_order_id" value="{{ $order->id }}" @checked($loop->first)>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox" name="source_order_ids[]" value="{{ $order->id }}">
                                </td>
                                <td class="font-monospace small">{{ $order->system_order_no }}</td>
                                <td class="font-monospace small text-muted">{{ $order->platform_order_no }}</td>
                                <td>{{ $order->buyer_nick ?: '-' }}</td>
                                <td class="text-center">{{ $order->items_count }}</td>
                                <td class="text-end">¥{{ number_format((float) $order->total_amount, 2) }}</td>
                                <td class="text-center">
                                    @php
                                        $statusMap = [
                                            'pending' => ['待处理', 'secondary'],
                                            'paid' => ['已付款', 'success'],
                                        ];
                                        [$label, $class] = $statusMap[$order->order_status] ?? [$order->order_status, 'light'];
                                    @endphp
                                    <span class="badge bg-{{ $class }}">{{ $label }}</span>
                                </td>
                                <td class="text-center">
                                    @php $count = (int) ($byBuyer[$order->buyer_nick] ?? 0); @endphp
                                    <span class="{{ $count > 1 ? 'text-primary fw-semibold' : 'text-muted' }}">{{ $count }}</span>
                                </td>
                            </tr>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fa fa-inbox fs-3 d-block mb-2"></i>
                                没有可合并的订单（仅待处理 / 已付款状态可合并）。
                            </div>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" onclick="return confirm('确认执行合并？被合并订单将变为已取消。')">
                    <i class="fa fa-object-group me-1"></i> 执行合并
                </button>
            </div>
        </form>

        {{ $orders->links() }}
    </div>
</div>
@endsection
