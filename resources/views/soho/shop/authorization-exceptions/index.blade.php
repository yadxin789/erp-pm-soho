@extends('soho::layouts.app')

@section('title', '授权异常列表')

@section('content')
<div class="container-fluid">

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-exclamation-triangle me-2"></i>授权异常管理
        </h4>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 总计 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-primary">{{ $statistics['total'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-list me-1"></i>异常总数
                    </div>
                </div>
            </div>
        </div>
        <!-- 待处理 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-danger">{{ $statistics['pending'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-clock me-1"></i>待处理
                    </div>
                </div>
            </div>
        </div>
        <!-- 处理中 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-warning">{{ $statistics['handling'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-cog me-1"></i>处理中
                    </div>
                </div>
            </div>
        </div>
        <!-- 已处理 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-success">{{ $statistics['handled'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-check-circle me-1"></i>已处理
                    </div>
                </div>
            </div>
        </div>
        <!-- 严重级别 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-danger">{{ $statistics['by_severity']['critical'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-fire me-1"></i>严重异常
                    </div>
                </div>
            </div>
        </div>
        <!-- 警告级别 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-warning">{{ $statistics['by_severity']['warning'] ?? 0 }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa fa-exclamation-circle me-1"></i>警告异常
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.shop.authorization-exceptions.index') }}" class="row g-3 align-items-end">
                <!-- 关键词搜索 -->
                <div class="col-md-2">
                    <label class="form-label">关键词搜索</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                           placeholder="标题/详情/错误信息">
                </div>
                <!-- 店铺筛选 -->
                <div class="col-md-2">
                    <label class="form-label">店铺</label>
                    <select class="form-select" name="shop_id">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->shop_name }} ({{ $shop->platform }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- 平台账号筛选 -->
                <div class="col-md-2">
                    <label class="form-label">平台账号</label>
                    <select class="form-select" name="platform_account_id">
                        <option value="">全部账号</option>
                        @foreach($platformAccounts as $account)
                            <option value="{{ $account->id }}" {{ request('platform_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_name }} ({{ $account->platform_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- 异常类型筛选 -->
                <div class="col-md-2">
                    <label class="form-label">异常类型</label>
                    <select class="form-select" name="exception_type">
                        <option value="">全部类型</option>
                        @foreach($exceptionTypeLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('exception_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- 异常级别筛选 -->
                <div class="col-md-1">
                    <label class="form-label">级别</label>
                    <select class="form-select" name="severity">
                        <option value="">全部</option>
                        @foreach($severityLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('severity') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- 状态筛选 -->
                <div class="col-md-1">
                    <label class="form-label">状态</label>
                    <select class="form-select" name="status">
                        <option value="">全部</option>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 日期范围 -->
                <div class="col-md-2">
                    <label class="form-label">开始日期</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">结束日期</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>

                <!-- 操作按钮 -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('soho.shop.authorization-exceptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times me-1"></i>重置
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 异常列表表格 -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fa fa-table me-2"></i>异常列表
                <span class="badge bg-secondary ms-1">{{ $exceptions->count() }} 条记录</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>标题</th>
                            <th>异常类型</th>
                            <th>级别</th>
                            <th>目标信息</th>
                            <th>状态</th>
                            <th>发生次数</th>
                            <th>异常时间</th>
                            <th width="120">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exceptions as $exception)
                            <tr>
                                <!-- ID -->
                                <td class="text-muted">{{ $exception->id }}</td>

                                <!-- 标题 -->
                                <td>
                                    <div class="fw-semibold">{{ $exception->title }}</div>
                                    @if($exception->error_message)
                                        <div class="small text-muted text-truncate" style="max-width: 250px;"
                                             title="{{ $exception->error_message }}">
                                            {{ $exception->error_message }}
                                        </div>
                                    @endif
                                </td>

                                <!-- 异常类型：图标 + 彩色徽章 -->
                                <td>
                                    @php
                                        $typeColor = $exceptionTypeColors[$exception->exception_type] ?? 'secondary';
                                        $typeIcon = $exceptionTypeIcons[$exception->exception_type] ?? 'fa-question';
                                        $typeLabel = $exceptionTypeLabels[$exception->exception_type] ?? $exception->exception_type;
                                    @endphp
                                    <span class="badge bg-{{ $typeColor }}">
                                        <i class="fa {{ $typeIcon }} me-1"></i>{{ $typeLabel }}
                                    </span>
                                </td>

                                <!-- 级别：彩色徽章 -->
                                <td>
                                    @php
                                        $severityColor = $severityColors[$exception->severity] ?? 'secondary';
                                        $severityLabel = $severityLabels[$exception->severity] ?? $exception->severity;
                                    @endphp
                                    <span class="badge bg-{{ $severityColor }}">
                                        {{ $severityLabel }}
                                    </span>
                                </td>

                                <!-- 目标信息 -->
                                <td>
                                    @php
                                        $targetLabel = $targetTypeLabels[$exception->target_type] ?? '-';
                                    @endphp
                                    <div class="small">
                                        <span class="badge bg-light text-dark me-1">{{ $targetLabel }}</span>
                                        @if($exception->shop)
                                            <span>{{ $exception->shop->shop_name }}</span>
                                        @elseif($exception->platformAccount)
                                            <span>{{ $exception->platformAccount->account_name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- 状态：彩色徽章 -->
                                <td>
                                    @php
                                        $statusColor = $statusColors[$exception->status] ?? 'secondary';
                                        $statusLabel = $statusLabels[$exception->status] ?? $exception->status;
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <!-- 发生次数 -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        <i class="fa fa-sync-alt me-1"></i>{{ $exception->occurrence_count ?? 1 }}
                                    </span>
                                </td>

                                <!-- 异常时间 -->
                                <td class="small">
                                    <div>{{ $exception->exception_at ? $exception->exception_at->format('Y-m-d H:i:s') : '-' }}</div>
                                    @if($exception->last_occurred_at && $exception->last_occurred_at->ne($exception->exception_at))
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            最近: {{ $exception->last_occurred_at->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- 操作 -->
                                <td>
                                    <a href="{{ route('soho.shop.authorization-exceptions.show', $exception) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye me-1"></i>详情
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fa fa-inbox fa-3x mb-3"></i>
                                    <div>暂无授权异常数据</div>
                                    <div class="small text-muted mt-1">当系统检测到授权异常时，将在此处显示</div>
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