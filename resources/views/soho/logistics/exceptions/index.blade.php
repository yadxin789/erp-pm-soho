@extends('soho::layouts.app')

{{-- 异常物流页面标题 --}}
@section('title', '异常物流')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">异常物流</h4>
            {{-- 面包屑导航 --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.exceptions.index') }}">物流管理</a></li>
                    <li class="breadcrumb-item active">异常物流</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('logistics.exceptions.index') }}">
                <div class="row g-2 align-items-end">
                    {{-- 运单号搜索 --}}
                    <div class="col-md-2">
                        <label class="form-label small">运单号</label>
                        <input type="text" class="form-control" name="waybill_no" placeholder="请输入运单号" value="{{ request('waybill_no') }}">
                    </div>
                    {{-- 异常类型 --}}
                    <div class="col-md-2">
                        <label class="form-label small">异常类型</label>
                        <select class="form-select" name="type">
                            <option value="">全部</option>
                            @foreach(($typeOptions ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 异常级别 --}}
                    <div class="col-md-2">
                        <label class="form-label small">异常级别</label>
                        <select class="form-select" name="level">
                            <option value="">全部</option>
                            @foreach(($levelOptions ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 状态 --}}
                    <div class="col-md-2">
                        <label class="form-label small">状态</label>
                        <select class="form-select" name="status">
                            <option value="">全部</option>
                            @foreach(($statusOptions ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 时间范围 --}}
                    <div class="col-md-2">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                        <a href="{{ route('logistics.exceptions.index') }}" class="btn btn-outline-secondary w-100 mt-1">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        {{-- 总异常数 --}}
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>总异常数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 待处理 --}}
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>待处理</small>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 处理中 --}}
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>处理中</small>
                    <h3 class="mb-0">{{ $stats['processing'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 已解决 --}}
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已解决</small>
                    <h3 class="mb-0">{{ $stats['resolved'] ?? 0 }}</h3>
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
                        <th>运单号</th>
                        <th>订单号</th>
                        <th>异常类型</th>
                        <th>异常级别</th>
                        <th>状态</th>
                        <th>发生时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions ?? $items ?? [] as $exception)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td><code>{{ $exception->waybill_no ?? '-' }}</code></td>
                            <td><code>{{ $exception->order_no ?? '-' }}</code></td>
                            <td>
                                @php
                                    // 异常类型图标映射
                                    $typeIcons = [
                                        'delayed'       => 'fa-clock',
                                        'lost'          => 'fa-box-open',
                                        'damaged'       => 'fa-triangle-exclamation',
                                        'wrong_address' => 'fa-location-dot',
                                        'refused'       => 'fa-ban',
                                        'other'         => 'fa-circle-info',
                                    ];
                                @endphp
                                <i class="fa {{ $typeIcons[$exception->type] ?? 'fa-circle-info' }} me-1"></i>
                                {{ ($typeOptions[$exception->type] ?? $exception->type) ?? '-' }}
                            </td>
                            <td>
                                @php
                                    // 异常级别颜色映射
                                    $levelColors = [
                                        'low'    => 'success',
                                        'medium' => 'info',
                                        'high'   => 'warning',
                                        'urgent' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $levelColors[$exception->level] ?? 'secondary' }}">
                                    {{ ($levelOptions[$exception->level] ?? $exception->level) ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    // 处理状态颜色映射
                                    $statusColors = [
                                        'pending'    => 'warning',
                                        'processing' => 'info',
                                        'resolved'   => 'success',
                                        'closed'     => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$exception->status] ?? 'secondary' }}">
                                    {{ ($statusOptions[$exception->status] ?? $exception->status) ?? '-' }}
                                </span>
                            </td>
                            <td class="small">{{ optional($exception->occurred_at ?? $exception->created_at)->format('Y-m-d H:i') }}</td>
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
        @if(isset($exceptions) && $exceptions->hasPages())
            <div class="card-footer">{{ $exceptions->links() }}</div>
        @elseif(isset($items) && $items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
