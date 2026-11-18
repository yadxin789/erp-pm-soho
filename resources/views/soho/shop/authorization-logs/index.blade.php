@extends('soho::layouts.app')

@section('title', '授权操作日志')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-history me-2"></i>授权操作日志
        </h4>
        <div>
            <a href="{{ route('soho.shop.authorization-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-refresh me-1"></i>刷新
            </a>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 总操作数 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">总操作数</div>
                    <div class="display-6 text-primary">{{ $statistics['total'] }}</div>
                </div>
            </div>
        </div>
        <!-- 成功操作 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">成功操作</div>
                    <div class="display-6 text-success">{{ $statistics['success'] }}</div>
                </div>
            </div>
        </div>
        <!-- 失败操作 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">失败操作</div>
                    <div class="display-6 text-danger">{{ $statistics['failed'] }}</div>
                </div>
            </div>
        </div>
        <!-- 今日操作 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">今日操作</div>
                    <div class="display-6 text-info">{{ $statistics['today'] }}</div>
                </div>
            </div>
        </div>
        <!-- 本周操作 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">本周操作</div>
                    <div class="display-6 text-warning">{{ $statistics['week'] }}</div>
                </div>
            </div>
        </div>
        <!-- 本月操作 -->
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">本月操作</div>
                    <div class="display-6 text-secondary">{{ $statistics['month'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.shop.authorization-logs.index') }}" class="row g-3 align-items-end">
                <!-- 关键词搜索 -->
                <div class="col-md-3">
                    <label class="form-label">关键词搜索</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="操作人/详情/错误信息">
                </div>
                <!-- 操作类型 -->
                <div class="col-md-2">
                    <label class="form-label">操作类型</label>
                    <select class="form-select" name="operation_type">
                        <option value="">全部</option>
                        @foreach($operationTypeLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('operation_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- 操作结果 -->
                <div class="col-md-2">
                    <label class="form-label">操作结果</label>
                    <select class="form-select" name="result">
                        <option value="">全部</option>
                        @foreach($resultLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('result') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- 目标类型 -->
                <div class="col-md-2">
                    <label class="form-label">目标类型</label>
                    <select class="form-select" name="target_type">
                        <option value="">全部</option>
                        @foreach($targetTypeLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('target_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> 筛选
                    </button>
                    <a href="{{ route('soho.shop.authorization-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i> 重置
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 日志列表 -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa fa-list me-2"></i>授权操作日志列表</h6>
            <span class="badge bg-secondary">共 {{ $logs->count() }} 条记录</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>操作人</th>
                            <th>操作类型</th>
                            <th>目标类型</th>
                            <th>目标信息</th>
                            <th>操作结果</th>
                            <th>操作时间</th>
                            <th>IP地址</th>
                            <th width="80">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted">#{{ $log->id }}</td>
                                <!-- 操作人 -->
                                <td>
                                    <div class="fw-semibold">
                                        <i class="fa fa-user-circle me-1 text-muted"></i>{{ $log->operator_name ?? '未知' }}
                                    </div>
                                    @if($log->operator)
                                        <div class="small text-muted">{{ $log->operator->email }}</div>
                                    @endif
                                </td>
                                <!-- 操作类型 - 彩色徽章 -->
                                <td>
                                    @php
                                        $opColor = $operationTypeColors[$log->operation_type] ?? 'secondary';
                                        $opLabel = $operationTypeLabels[$log->operation_type] ?? $log->operation_type;
                                        $opIcons = [
                                            'authorize' => 'fa-link',
                                            'reauthorize' => 'fa-sync',
                                            'revoke' => 'fa-unlink',
                                            'refresh' => 'fa-redo',
                                            'token_refresh' => 'fa-key',
                                        ];
                                        $opIcon = $opIcons[$log->operation_type] ?? 'fa-cog';
                                    @endphp
                                    <span class="badge bg-{{ $opColor }}">
                                        <i class="fa {{ $opIcon }} me-1"></i>{{ $opLabel }}
                                    </span>
                                </td>
                                <!-- 目标类型 -->
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $targetTypeLabels[$log->target_type] ?? $log->target_type }}
                                    </span>
                                </td>
                                <!-- 目标信息 -->
                                <td>
                                    @if($log->target_type === 'shop' && $log->shop)
                                        <div class="fw-semibold">{{ $log->shop->shop_name }}</div>
                                        <div class="small text-muted">{{ $log->shop->platform }}</div>
                                    @elseif($log->target_type === 'platform_account' && $log->platformAccount)
                                        <div class="fw-semibold">{{ $log->platformAccount->account_name }}</div>
                                        <div class="small text-muted">{{ $log->platformAccount->platform_name }}</div>
                                    @elseif($log->detail)
                                        <div class="small text-truncate" style="max-width: 250px;">{{ $log->detail }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <!-- 操作结果 - 彩色徽章 -->
                                <td>
                                    @php
                                        $resultColor = $resultColors[$log->result] ?? 'secondary';
                                        $resultLabel = $resultLabels[$log->result] ?? $log->result;
                                    @endphp
                                    <span class="badge bg-{{ $resultColor }}">
                                        <i class="fa {{ $log->result === 'success' ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                        {{ $resultLabel }}
                                    </span>
                                </td>
                                <!-- 操作时间 -->
                                <td class="small">
                                    <i class="fa fa-clock-o me-1 text-muted"></i>
                                    {{ $log->operated_at ? $log->operated_at->format('Y-m-d H:i:s') : '-' }}
                                </td>
                                <!-- IP地址 -->
                                <td>
                                    @if($log->ip_address)
                                        <code class="small">{{ $log->ip_address }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <!-- 操作按钮 -->
                                <td>
                                    <a href="{{ route('soho.shop.authorization-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fa fa-inbox fa-3x mb-3"></i>
                                    <div>暂无授权操作日志</div>
                                    <div class="small">当店铺或平台账号进行授权操作时，会在此处显示记录</div>
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