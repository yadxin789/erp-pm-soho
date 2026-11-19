@extends('soho::layouts.app')

{{-- 对账差异审计列表视图 --}}
@section('title', '对账差异审计')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">对账差异审计</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审计中心</li>
                    <li class="breadcrumb-item active">对账差异审计</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.audit.reconciliation-diff') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="search" placeholder="对账单号" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">平台</label>
                        <select class="form-select" name="platform">
                            <option value="">全部</option>
                            @foreach($platformOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('platform') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">差异类型</label>
                        <select class="form-select" name="diff_type">
                            <option value="">全部</option>
                            @foreach($diffTypeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('diff_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">状态</label>
                        <select class="form-select" name="status">
                            <option value="">全部</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.audit.reconciliation-diff') }}" class="btn btn-outline-secondary btn-sm">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>差异总数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>待处理</small>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已处理</small>
                    <h3 class="mb-0">{{ $stats['resolved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>差异金额合计</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_amount'] ?? 0, 2) }}</h3>
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
                        <th>对账单号</th>
                        <th>平台</th>
                        <th>差异类型</th>
                        <th>差异金额</th>
                        <th>处理状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 处理状态对应的 badge 颜色 --}}
                    @php
                        $statusColors = [
                            'pending'  => 'warning',
                            'resolved' => 'success',
                        ];
                    @endphp
                    @forelse($reconciliationDiffs as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $reconciliationDiffs->firstItem() + $index }}</td>
                            <td><code>{{ $item->reconciliation_no ?? '-' }}</code></td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $platformOptions[$item->platform] ?? $item->platform ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $diffTypeOptions[$item->diff_type] ?? $item->diff_type ?? '-' }}</td>
                            <td class="fw-semibold text-danger">¥{{ number_format($item->diff_amount ?? 0, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$item->status] ?? $item->status }}
                                </span>
                            </td>
                            <td class="small">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i') : '-' }}</td>
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
        @if($reconciliationDiffs->total() > 0)
            <div class="card-footer">{{ $reconciliationDiffs->links() }}</div>
        @endif
    </div>
@endsection
