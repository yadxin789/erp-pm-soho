@extends('soho::layouts.app')

@section('title', '授权失败记录')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-exclamation-triangle me-2"></i>授权失败记录
        </h4>
        <div>
            <span class="text-muted small">
                <i class="fa fa-info-circle me-1"></i>监控授权异常，及时处理授权失败问题
            </span>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 总数 -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-list me-1"></i>记录总数</div>
                            <div class="display-6 text-primary">{{ $statistics['total'] }}</div>
                        </div>
                        <div class="text-primary opacity-25">
                            <i class="fa fa-file-alt fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 待处理 -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-clock me-1"></i>待处理</div>
                            <div class="display-6 text-danger">{{ $statistics['pending'] }}</div>
                        </div>
                        <div class="text-danger opacity-25">
                            <i class="fa fa-hourglass-half fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 处理中 -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-cogs me-1"></i>处理中</div>
                            <div class="display-6 text-warning">{{ $statistics['resolving'] }}</div>
                        </div>
                        <div class="text-warning opacity-25">
                            <i class="fa fa-spinner fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 已解决 -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-check-circle me-1"></i>已解决</div>
                            <div class="display-6 text-success">{{ $statistics['resolved'] }}</div>
                        </div>
                        <div class="text-success opacity-25">
                            <i class="fa fa-check fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 已忽略 -->
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-ban me-1"></i>已忽略</div>
                            <div class="display-6 text-secondary">{{ $statistics['ignored'] }}</div>
                        </div>
                        <div class="text-secondary opacity-25">
                            <i class="fa fa-ban fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 今日新增 -->
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-calendar-day me-1"></i>今日新增</div>
                            <div class="display-6 text-info">{{ $statistics['today'] }}</div>
                        </div>
                        <div class="text-info opacity-25">
                            <i class="fa fa-calendar fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 本周新增 -->
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1"><i class="fa fa-calendar-week me-1"></i>本周新增</div>
                            <div class="display-6" style="color:#6f42c1;">{{ $statistics['week'] }}</div>
                        </div>
                        <div style="color:#6f42c1;" class="opacity-25">
                            <i class="fa fa-calendar-check fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-filter me-2"></i>筛选条件</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('soho.shop.authorization-failures.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">关键词</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="错误信息 / 错误码 / 平台">
                </div>
                <div class="col-md-2">
                    <label class="form-label">失败类型</label>
                    <select class="form-select" name="failure_type">
                        <option value="">全部</option>
                        @foreach($failureTypeLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('failure_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">状态</label>
                    <select class="form-select" name="status">
                        <option value="">全部</option>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">平台</label>
                    <select class="form-select" name="platform">
                        <option value="">全部</option>
                        @foreach($platforms as $platform)
                            <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">开始日期</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">结束日期</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-1"></i>查询
                    </button>
                    <a href="{{ route('soho.shop.authorization-failures.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times me-1"></i>重置
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 授权失败记录列表 -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa fa-list me-2"></i>授权失败记录</h6>
            <span class="badge bg-light text-dark">共 {{ $failures->count() }} 条记录</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>店铺名称</th>
                            <th>平台</th>
                            <th>失败类型</th>
                            <th>目标类型</th>
                            <th>错误码</th>
                            <th>错误信息</th>
                            <th>状态</th>
                            <th>重试次数</th>
                            <th>失败时间</th>
                            <th width="100">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failures as $failure)
                            <tr>
                                <td class="text-muted">#{{ $failure->id }}</td>
                                <td>
                                    @if($failure->shop)
                                        <div class="fw-semibold">{{ $failure->shop->shop_name }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $failure->platform }}</span>
                                </td>
                                <td>
                                    @php
                                        $failureColor = $failureTypeColors[$failure->failure_type] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $failureColor }}">
                                        {{ $failureTypeLabels[$failure->failure_type] ?? $failure->failure_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $targetTypeLabels[$failure->target_type] ?? $failure->target_type }}
                                    </span>
                                </td>
                                <td>
                                    <code class="small">{{ $failure->error_code ?: '-' }}</code>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $failure->error_message }}">
                                        {{ \Illuminate\Support\Str::limit($failure->error_message, 50) ?: '-' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColor = $statusColors[$failure->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $statusLabels[$failure->status] ?? $failure->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $failure->retry_count ?? 0 }}{{ $failure->max_retries ? ' / ' . $failure->max_retries : '' }}
                                    </span>
                                </td>
                                <td class="small">
                                    {{ $failure->failed_at ? $failure->failed_at->format('Y-m-d H:i:s') : '-' }}
                                </td>
                                <td>
                                    <a href="{{ route('soho.shop.authorization-failures.show', $failure) }}" class="btn btn-sm btn-outline-primary" title="查看详情">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($failure->status === 'pending' || $failure->status === 'resolving')
                                        <form method="POST" action="{{ route('soho.shop.authorization-failures.resolve', $failure) }}" class="d-inline" onsubmit="return confirm('确认将此记录标记为已解决？')">
                                            @csrf
                                            <input type="hidden" name="resolution_note" value="系统快速标记">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="标记已解决">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="fa fa-inbox fa-3x mb-2"></i>
                                    <div>暂无授权失败记录</div>
                                    <small class="text-muted">当店铺授权出现异常时，会自动在此处显示</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
