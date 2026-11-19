@extends('soho::layouts.app')

{{-- 售后明细报表视图 --}}
@section('title', '售后明细报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">售后明细报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">数据报表</li>
                    <li class="breadcrumb-item active">售后明细</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.aftersale-detail') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">售后类型</label>
                    <select name="type" class="form-select">
                        <option value="">全部</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">状态</label>
                    <select name="status" class="form-select">
                        <option value="">全部</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">店铺</label>
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.aftersale-detail') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>售后单总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>退款总金额</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_amount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>待处理数</small>
                    <h3 class="mb-0">{{ number_format($stats['pending_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已完成数</small>
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
                        <th>售后单号</th>
                        <th>订单号</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th>店铺</th>
                        <th class="text-end">金额</th>
                        <th class="text-end">处理时长(小时)</th>
                        <th>申请时间</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusColors = [
                            'pending' => 'warning', 'processing' => 'info',
                            'approved' => 'primary', 'rejected' => 'danger',
                            'completed' => 'success', 'cancelled' => 'secondary',
                        ];
                    @endphp
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->after_sale_no }}</code></td>
                            <td>{{ $item->platform_order_no ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $typeOptions[$item->type] ?? $item->type }}</span></td>
                            <td><span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">{{ $statusOptions[$item->status] ?? $item->status }}</span></td>
                            <td>{{ $item->shop_name ?? '-' }}</td>
                            <td class="text-end">¥{{ number_format($item->refund_amount, 2) }}</td>
                            <td class="text-end">
                                @if($item->handle_hours !== null)
                                    @php
                                        $hourColor = $item->handle_hours <= 24 ? 'success' : ($item->handle_hours <= 72 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $hourColor }}">{{ number_format($item->handle_hours, 2) }}</span>
                                @else
                                    <span class="text-muted">未处理</span>
                                @endif
                            </td>
                            <td class="small">{{ optional($item->apply_time)->format('Y-m-d H:i') }}</td>
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
        @if($items->total() > 0)
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
