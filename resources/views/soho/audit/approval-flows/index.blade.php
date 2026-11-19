@extends('soho::layouts.app')

{{-- 审批流程审计列表视图 --}}
@section('title', '审批流程审计')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">审批流程审计</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审计中心</li>
                    <li class="breadcrumb-item active">审批流程审计</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.audit.approval-flow') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="search" placeholder="审批单号/申请人/审批人" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">审批类型</label>
                        <select class="form-select" name="type">
                            <option value="">全部</option>
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">审批结果</label>
                        <select class="form-select" name="result">
                            <option value="">全部</option>
                            @foreach($resultOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('result') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
                    <a href="{{ route('soho.audit.approval-flow') }}" class="btn btn-outline-secondary btn-sm">重置筛选</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>审批总数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已通过</small>
                    <h3 class="mb-0">{{ $stats['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>已拒绝</small>
                    <h3 class="mb-0">{{ $stats['rejected'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>今日审批</small>
                    <h3 class="mb-0">{{ $stats['today'] ?? 0 }}</h3>
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
                        <th>审批单号</th>
                        <th>类型</th>
                        <th>申请人</th>
                        <th>审批人</th>
                        <th>审批时间</th>
                        <th>结果</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 审批结果对应的 badge 颜色 --}}
                    @php
                        $resultColors = [
                            'approved' => 'success',
                            'rejected' => 'danger',
                        ];
                    @endphp
                    @forelse($approvalFlows as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $approvalFlows->firstItem() + $index }}</td>
                            <td><code>{{ $item->approval_no ?? '-' }}</code></td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $typeOptions[$item->type] ?? $item->type ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $item->applicant ?? '-' }}</td>
                            <td>{{ $item->approver ?? '-' }}</td>
                            <td class="small">{{ $item->approved_at ? \Carbon\Carbon::parse($item->approved_at)->format('Y-m-d H:i') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $resultColors[$item->result] ?? 'secondary' }}">
                                    {{ $resultOptions[$item->result] ?? $item->result }}
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
        @if($approvalFlows->total() > 0)
            <div class="card-footer">{{ $approvalFlows->links() }}</div>
        @endif
    </div>
@endsection
