@extends('soho::layouts.app')

@section('title', '采购计划')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">采购计划</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">采购计划</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 搜索/筛选表单 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.procurement.plans.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">计划名称</label>
                        <input type="text" class="form-control" name="name" placeholder="请输入计划名称" value="{{ request('name') }}">
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
                    <div class="col-md-2">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.plans.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>总计划数</small>
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
                    <small>已审批</small>
                    <h3 class="mb-0">{{ $stats['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>已完成</small>
                    <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>计划编号</th>
                        <th>计划名称</th>
                        <th>类型</th>
                        <th>负责人</th>
                        <th>预算金额</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td class="text-muted">{{ $plan->id }}</td>
                            <td><code>{{ $plan->plan_no ?? 'PL-' . $plan->id }}</code></td>
                            <td>{{ $plan->name }}</td>
                            <td>{{ $plan->type ?? '-' }}</td>
                            <td>{{ $plan->owner ?? '-' }}</td>
                            <td>¥{{ number_format($plan->budget ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'completed' => 'info',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$plan->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$plan->status] ?? $plan->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($plan->created_at)->format('Y-m-d H:i') }}</td>
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
        @if($plans->hasPages())
            <div class="card-footer">{{ $plans->links() }}</div>
        @endif
    </div>
@endsection
