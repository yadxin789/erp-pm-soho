@extends('soho::layouts.app')

@section('title', 'Token刷新记录')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-refresh me-2"></i>Token刷新记录
        </h4>
        <div>
            <a href="{{ route('soho.shop.token-refresh-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-refresh me-1"></i>刷新
            </a>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 总刷新次数 -->
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">总刷新</h6>
                            <h4 class="mb-0">{{ $statistics['total'] }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fa fa-database fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 成功次数 -->
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">成功</h6>
                            <h4 class="mb-0 text-success">{{ $statistics['success'] }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fa fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 失败次数 -->
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">失败</h6>
                            <h4 class="mb-0 text-danger">{{ $statistics['failed'] }}</h4>
                        </div>
                        <div class="text-danger">
                            <i class="fa fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 今日刷新 -->
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">今日</h6>
                            <h4 class="mb-0 text-info">{{ $statistics['today'] }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fa fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 本周刷新 -->
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">本周</h6>
                            <h4 class="mb-0 text-warning">{{ $statistics['week'] }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fa fa-calendar-check-o fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 本月刷新 -->
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">本月</h6>
                            <h4 class="mb-0 text-purple">{{ $statistics['month'] }}</h4>
                        </div>
                        <div style="color: #6f42c1;">
                            <i class="fa fa-calendar-o fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 按刷新方式统计 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cogs me-2"></i>按刷新方式统计</h6>
                </div>
                <div class="card-body">
                    @if(!empty($statistics['by_method']))
                        <div class="row text-center">
                            @foreach($refreshMethodLabels as $method => $label)
                                <div class="col">
                                    <div class="display-5 text-{{ $refreshMethodColors[$method] ?? 'secondary' }}">
                                        {{ $statistics['by_method'][$method] ?? 0 }}
                                    </div>
                                    <div class="small text-muted">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-2">暂无数据</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 按平台统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>按平台统计</h6>
                </div>
                <div class="card-body">
                    @if(!empty($statistics['by_platform']))
                        <div class="row text-center">
                            @foreach($statistics['by_platform'] as $platform => $count)
                                <div class="col">
                                    <div class="display-5 text-dark">{{ $count }}</div>
                                    <div class="small text-muted">{{ $platform }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-2">暂无数据</div>
                    @endif
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
            <form method="GET" action="{{ route('soho.shop.token-refresh-logs.index') }}" class="row g-3">
                <!-- 搜索关键词 -->
                <div class="col-md-3">
                    <label class="form-label">搜索</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="错误信息/错误码/平台">
                </div>
                <!-- 刷新结果 -->
                <div class="col-md-2">
                    <label class="form-label">刷新结果</label>
                    <select class="form-select" name="result">
                        <option value="">全部</option>
                        @foreach($resultLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('result') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- 刷新方式 -->
                <div class="col-md-2">
                    <label class="form-label">刷新方式</label>
                    <select class="form-select" name="refresh_method">
                        <option value="">全部</option>
                        @foreach($refreshMethodLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('refresh_method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- 平台 -->
                <div class="col-md-2">
                    <label class="form-label">平台</label>
                    <select class="form-select" name="platform">
                        <option value="">全部</option>
                        @foreach($platforms as $platform)
                            <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- 开始日期 -->
                <div class="col-md-2">
                    <label class="form-label">开始日期</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <!-- 结束日期 -->
                <div class="col-md-2">
                    <label class="form-label">结束日期</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <!-- 操作按钮 -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('soho.shop.token-refresh-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 日志列表 -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa fa-list me-2"></i>刷新记录列表</h6>
            <div class="text-muted small">共 {{ $logs->count() }} 条记录</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>店铺名称</th>
                            <th>平台</th>
                            <th>刷新方式</th>
                            <th>结果</th>
                            <th>新Token过期时间</th>
                            <th>旧Token过期时间</th>
                            <th>刷新时间</th>
                            <th>操作人</th>
                            <th width="80">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted">{{ $log->id }}</td>
                                <td>
                                    @if($log->shop)
                                        <div class="fw-semibold">{{ $log->shop->shop_name }}</div>
                                    @elseif($log->platformAccount)
                                        <div class="fw-semibold">{{ $log->platformAccount->account_name }}</div>
                                        <span class="badge bg-light text-dark">{{ $targetTypeLabels[$log->target_type] ?? $log->target_type }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $log->platform }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $refreshMethodColors[$log->refresh_method] ?? 'secondary' }}">
                                        {{ $refreshMethodLabels[$log->refresh_method] ?? $log->refresh_method }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $resultColors[$log->result] ?? 'secondary' }}">
                                        {{ $resultLabels[$log->result] ?? $log->result }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->new_token_expires_at)
                                        <div class="small">{{ $log->new_token_expires_at->format('Y-m-d H:i') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->old_token_expires_at)
                                        <div class="small">{{ $log->old_token_expires_at->format('Y-m-d H:i') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($log->refreshed_at)
                                        {{ $log->refreshed_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->operator)
                                        <div>{{ $log->operator->name }}</div>
                                        <div class="small text-muted">{{ $log->operator->email }}</div>
                                    @else
                                        <span class="text-muted">系统</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('soho.shop.token-refresh-logs.show', $log) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2"></i>
                                    <div>暂无刷新记录</div>
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

@section('scripts')
<script>
    // 自动隐藏提示消息
    document.addEventListener('DOMContentLoaded', function() {
        // 表单提交时禁用按钮防止重复提交
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function() {
                var submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    var originalText = submitBtn.innerHTML;
                    setTimeout(function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 5000);
                }
            });
        });
    });
</script>
@endsection