@extends('soho::layouts.app')

{{-- 售后争议列表视图 --}}
@section('title', '售后争议')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">售后争议</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">售后争议</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.aftersale.dispute') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="keyword" placeholder="争议单号/订单号/客户名称" value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">争议类型</label>
                        <select class="form-select" name="type">
                            <option value="">全部</option>
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">级别</label>
                        <select class="form-select" name="level">
                            <option value="">全部</option>
                            @foreach($levelOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
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
                    <div class="col-md-1">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-12">
                        <a href="{{ route('soho.aftersale.dispute') }}" class="btn btn-outline-secondary btn-sm">重置筛选</a>
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
                    <small>争议总数</small>
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
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>处理中</small>
                    <h3 class="mb-0">{{ $stats['processing'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
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
                        <th>争议单号</th>
                        <th>订单号</th>
                        <th>争议类型</th>
                        <th>争议级别</th>
                        <th>客户名称</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 状态与级别对应的 badge 颜色 --}}
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'resolved' => 'success',
                            'escalated' => 'danger',
                        ];
                        $levelColors = [
                            'low' => 'secondary',
                            'medium' => 'info',
                            'high' => 'warning',
                            'critical' => 'danger',
                        ];
                    @endphp
                    @forelse($disputes as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->dispute_no ?? '-' }}</code></td>
                            <td>{{ $item->order_no ?? '-' }}</td>
                            <td>{{ $typeOptions[$item->type] ?? $item->type ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $levelColors[$item->level] ?? 'secondary' }}">
                                    {{ $levelOptions[$item->level] ?? $item->level ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $item->customer_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$item->status] ?? $item->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- 分页 --}}
        @if($disputes->total() > 0)
            <div class="card-footer">{{ $disputes->links() }}</div>
        @endif
    </div>
@endsection
