@extends('soho::layouts.app')

{{-- 物流轨迹页面标题 --}}
@section('title', '物流轨迹')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">物流轨迹</h4>
            {{-- 面包屑导航 --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('soho.logistics.tracking') }}">物流管理</a></li>
                    <li class="breadcrumb-item active">物流轨迹</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.logistics.tracking') }}">
                <div class="row g-2 align-items-end">
                    {{-- 运单号搜索 --}}
                    <div class="col-md-3">
                        <label class="form-label small">运单号</label>
                        <input type="text" class="form-control" name="waybill_no" placeholder="请输入运单号" value="{{ request('waybill_no') }}">
                    </div>
                    {{-- 快递公司 --}}
                    <div class="col-md-2">
                        <label class="form-label small">快递公司</label>
                        <select class="form-select" name="express_company">
                            <option value="">全部</option>
                            @foreach(($expressCompanies ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('express_company') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 轨迹状态 --}}
                    <div class="col-md-2">
                        <label class="form-label small">轨迹状态</label>
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
                    <div class="col-md-2">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                        <a href="{{ route('soho.logistics.tracking') }}" class="btn btn-outline-secondary w-100 mt-1">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        {{-- 总轨迹数 --}}
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>总轨迹数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 运输中 --}}
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>运输中</small>
                    <h3 class="mb-0">{{ $stats['in_transit'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 已签收 --}}
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已签收</small>
                    <h3 class="mb-0">{{ $stats['delivered'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 异常轨迹 --}}
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>异常轨迹</small>
                    <h3 class="mb-0">{{ $stats['exception'] ?? 0 }}</h3>
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
                        <th>快递公司</th>
                        <th>订单号</th>
                        <th>轨迹状态</th>
                        <th>最后更新时间</th>
                        <th>更新内容</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackings ?? $items ?? [] as $tracking)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td><code>{{ $tracking->waybill_no ?? '-' }}</code></td>
                            <td>{{ $tracking->express_company ?? '-' }}</td>
                            <td><code>{{ $tracking->order_no ?? '-' }}</code></td>
                            <td>
                                @php
                                    // 轨迹状态颜色映射
                                    $statusColors = [
                                        'in_transit' => 'info',
                                        'delivered'  => 'success',
                                        'exception'  => 'danger',
                                        'unknown'    => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$tracking->status] ?? 'secondary' }}">
                                    {{ ($statusOptions[$tracking->status] ?? $tracking->status) ?? '-' }}
                                </span>
                            </td>
                            <td class="small">{{ optional($tracking->updated_at ?? $tracking->last_updated_at ?? null)->format('Y-m-d H:i') }}</td>
                            <td class="small text-muted">{{ $tracking->content ?? $tracking->description ?? '-' }}</td>
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
        @if(isset($trackings) && $trackings->hasPages())
            <div class="card-footer">{{ $trackings->links() }}</div>
        @elseif(isset($items) && $items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
