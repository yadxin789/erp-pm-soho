@extends('soho::layouts.app')

{{-- 订单状态审计列表视图 --}}
@section('title', '订单状态审计')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">订单状态审计</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审计中心</li>
                    <li class="breadcrumb-item active">订单状态审计</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.audit.order-status') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="search" placeholder="订单号/操作人" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">旧状态</label>
                        <select class="form-select" name="old_status">
                            <option value="">全部</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('old_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">新状态</label>
                        <select class="form-select" name="new_status">
                            <option value="">全部</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('new_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('soho.audit.order-status') }}" class="btn btn-outline-secondary btn-sm">重置筛选</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>状态变更总数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>今日变更</small>
                    <h3 class="mb-0">{{ $stats['today'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>本周变更</small>
                    <h3 class="mb-0">{{ $stats['week'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>取消订单数</small>
                    <h3 class="mb-0">{{ $stats['cancel'] ?? 0 }}</h3>
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
                        <th>旧状态</th>
                        <th>新状态</th>
                        <th>操作人</th>
                        <th>操作时间</th>
                        <th>IP地址</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orderStatuses as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $orderStatuses->firstItem() + $index }}</td>
                            <td><code>{{ $item->order_no ?? '-' }}</code></td>
                            <td>
                                @if($item->old_status)
                                    <span class="badge bg-secondary">{{ $statusOptions[$item->old_status] ?? $item->old_status }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->new_status)
                                    <span class="badge bg-primary">{{ $statusOptions[$item->new_status] ?? $item->new_status }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->operator ?? '-' }}</td>
                            <td class="small">{{ $item->operated_at ? \Carbon\Carbon::parse($item->operated_at)->format('Y-m-d H:i') : '-' }}</td>
                            <td><code class="small">{{ $item->ip_address ?? '-' }}</code></td>
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
        {{-- 分页 --}}
        @if($orderStatuses->hasPages())
            <div class="card-footer">{{ $orderStatuses->links() }}</div>
        @endif
    </div>
@endsection
