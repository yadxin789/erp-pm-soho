@extends('soho::layouts.app')

@section('title', '采购申请')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">采购申请</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">采购申请</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 搜索/筛选 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">申请单号/主题</label>
                        <input type="text" class="form-control" name="search" placeholder="请输入关键词" value="{{ request('search') }}">
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
                        <label class="form-label small">申请人</label>
                        <input type="text" class="form-control" name="applicant" value="{{ request('applicant') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.requests.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body py-3"><small>总申请数</small><h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark"><div class="card-body py-3"><small>待审批</small><h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body py-3"><small>已审批</small><h3 class="mb-0">{{ $stats['approved'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white"><div class="card-body py-3"><small>已驳回</small><h3 class="mb-0">{{ $stats['rejected'] ?? 0 }}</h3></div></div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>申请单号</th>
                        <th>主题</th>
                        <th>申请人</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th>申请时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td class="text-muted">{{ $request->id }}</td>
                            <td><code>{{ $request->request_no ?? 'REQ-' . $request->id }}</code></td>
                            <td>{{ $request->subject ?? $request->title ?? '-' }}</td>
                            <td>{{ $request->applicant ?? '-' }}</td>
                            <td>¥{{ number_format($request->amount ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'converted' => 'info'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$request->status] ?? $request->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($request->created_at)->format('Y-m-d H:i') }}</td>
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
        @if($requests->hasPages())
            <div class="card-footer">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
