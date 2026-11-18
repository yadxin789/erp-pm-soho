@extends('soho::layouts.app')

{{-- 面单管理页面标题 --}}
@section('title', '面单管理')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">面单管理</h4>
            {{-- 面包屑导航 --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.waybills.index') }}">物流管理</a></li>
                    <li class="breadcrumb-item active">面单管理</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('logistics.waybills.index') }}">
                <div class="row g-2 align-items-end">
                    {{-- 关键词搜索 --}}
                    <div class="col-md-3">
                        <label class="form-label small">关键词搜索</label>
                        <input type="text" class="form-control" name="keyword" placeholder="运单号/订单号/收件人" value="{{ request('keyword') }}">
                    </div>
                    {{-- 快递公司选择 --}}
                    <div class="col-md-2">
                        <label class="form-label small">快递公司</label>
                        <select class="form-select" name="express_company">
                            <option value="">全部</option>
                            @foreach(($expressCompanies ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('express_company') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 面单状态 --}}
                    <div class="col-md-2">
                        <label class="form-label small">面单状态</label>
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
                        <a href="{{ route('logistics.waybills.index') }}" class="btn btn-outline-secondary w-100 mt-1">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        {{-- 总面单数 --}}
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>总面单数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 已打印 --}}
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已打印</small>
                    <h3 class="mb-0">{{ $stats['printed'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 待打印 --}}
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>待打印</small>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 异常面单 --}}
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>异常面单</small>
                    <h3 class="mb-0">{{ $stats['abnormal'] ?? 0 }}</h3>
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
                        <th>快递公司</th>
                        <th>收件人</th>
                        <th>面单状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waybills ?? $items ?? [] as $waybill)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td><code>{{ $waybill->waybill_no ?? '-' }}</code></td>
                            <td><code>{{ $waybill->order_no ?? '-' }}</code></td>
                            <td>{{ $waybill->express_company ?? '-' }}</td>
                            <td>{{ $waybill->receiver_name ?? '-' }}</td>
                            <td>
                                @php
                                    // 面单状态颜色映射
                                    $statusColors = [
                                        'pending'   => 'warning',
                                        'printed'   => 'success',
                                        'voided'    => 'secondary',
                                        'failed'    => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$waybill->status] ?? 'secondary' }}">
                                    {{ ($statusOptions[$waybill->status] ?? $waybill->status) ?? '-' }}
                                </span>
                            </td>
                            <td class="small">{{ optional($waybill->created_at)->format('Y-m-d H:i') }}</td>
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
        @if(isset($waybills) && $waybills->hasPages())
            <div class="card-footer">{{ $waybills->links() }}</div>
        @elseif(isset($items) && $items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
