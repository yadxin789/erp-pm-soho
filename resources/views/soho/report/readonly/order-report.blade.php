@extends('soho::layouts.app')

{{-- 只读订单报表视图 --}}
@section('title', '订单报表（只读）')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                订单报表
                <span class="badge bg-secondary ms-2">只读</span>
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">只读报表</li>
                    <li class="breadcrumb-item active">订单报表</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.readonly.order.report') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">店铺</label>
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.readonly.order.report') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>订单总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>总销售额</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_sales'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>已支付订单</small>
                    <h3 class="mb-0">{{ number_format($stats['paid_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>已完成订单</small>
                    <h3 class="mb-0">{{ number_format($stats['completed_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>订单号</th>
                        <th>店铺</th>
                        <th>平台</th>
                        <th>订单状态</th>
                        <th>支付状态</th>
                        <th class="text-end">订单金额</th>
                        <th class="text-end">实付金额</th>
                        <th>下单时间</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $orderStatusColors = [
                            'pending' => 'warning', 'paid' => 'info',
                            'shipped' => 'primary', 'completed' => 'success',
                            'cancelled' => 'danger', 'closed' => 'secondary',
                        ];
                        $payStatusColors = [
                            'unpaid' => 'danger', 'paid' => 'success',
                            'refunded' => 'warning', 'partial' => 'info',
                        ];
                    @endphp
                    @forelse($orders as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->platform_order_no }}</code></td>
                            <td>{{ $item->shop_name ?? '-' }}</td>
                            <td>{{ $item->platform ?? '-' }}</td>
                            <td><span class="badge bg-{{ $orderStatusColors[$item->order_status] ?? 'secondary' }}">{{ $item->order_status }}</span></td>
                            <td><span class="badge bg-{{ $payStatusColors[$item->pay_status] ?? 'secondary' }}">{{ $item->pay_status }}</span></td>
                            <td class="text-end">¥{{ number_format($item->total_amount, 2) }}</td>
                            <td class="text-end text-success">¥{{ number_format($item->pay_amount, 2) }}</td>
                            <td class="small">{{ optional($item->order_created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
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
