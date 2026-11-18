@extends('soho::layouts.app')

{{-- 大额差异审批列表视图 --}}
@section('title', '大额差异审批')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">大额差异审批</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审批中心</li>
                    <li class="breadcrumb-item active">大额差异审批</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.approval.finance-diff.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="search" placeholder="单号/申请人" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">类型</label>
                        <select class="form-select" name="type">
                            <option value="">全部</option>
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
                    <div class="col-md-2">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.approval.finance-diff.index') }}" class="btn btn-outline-secondary">重置</a>
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
                    <small>审批申请总数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>待审批</small>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已批准</small>
                    <h3 class="mb-0">{{ $stats['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>差异金额合计</small>
                    <h3 class="mb-0">¥{{ number_format($totalDiffAmount ?? 0, 2) }}</h3>
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
                        <th>单号</th>
                        <th>类型</th>
                        <th>金额</th>
                        <th>差异金额</th>
                        <th>申请人</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 状态对应的 badge 颜色 --}}
                    @php
                        $statusColors = [
                            'pending'  => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                        ];
                    @endphp
                    @forelse($approvals as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $approvals->firstItem() + $index }}</td>
                            <td><code>{{ $item->doc_no ?? '-' }}</code></td>
                            <td>
                                @if(isset($item->type) && isset($typeOptions[$item->type]))
                                    <span class="badge bg-secondary">{{ $typeOptions[$item->type] }}</span>
                                @else
                                    {{ $item->type ?? '-' }}
                                @endif
                            </td>
                            <td>¥{{ number_format($item->amount ?? 0, 2) }}</td>
                            <td>
                                @if(isset($item->diff_amount))
                                    <span class="text-{{ $item->diff_amount >= 0 ? 'danger' : 'success' }}">
                                        ¥{{ number_format($item->diff_amount, 2) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->applicant_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$item->status] ?? $item->status }}
                                </span>
                            </td>
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
        @if($approvals->hasPages())
            <div class="card-footer">{{ $approvals->links() }}</div>
        @endif
    </div>
@endsection
