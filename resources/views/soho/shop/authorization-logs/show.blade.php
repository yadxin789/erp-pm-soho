@extends('soho::layouts.app')

@section('title', '授权操作日志详情')

@section('content')
<div class="container-fluid">
    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.shop.authorization-logs.index') }}">授权操作日志</a></li>
            <li class="breadcrumb-item active">日志详情 #{{ $log->id }}</li>
        </ol>
    </nav>

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-file-text-o me-2"></i>日志详情 #{{ $log->id }}
        </h4>
        <div>
            <a href="{{ route('soho.shop.authorization-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>
    </div>

    <!-- 顶部状态横幅 -->
    <div class="card mb-3">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <!-- 操作类型徽章 -->
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
                    $resultColor = $resultColors[$log->result] ?? 'secondary';
                    $resultLabel = $resultLabels[$log->result] ?? $log->result;
                    $targetLabel = $targetTypeLabels[$log->target_type] ?? $log->target_type;
                @endphp
                <span class="badge bg-{{ $opColor }} fs-6 me-3">
                    <i class="fa {{ $opIcon }} me-1"></i>{{ $opLabel }}
                </span>
                <span class="text-muted me-3">→</span>
                <span class="badge bg-light text-dark fs-6 me-3">
                    <i class="fa fa-tag me-1"></i>{{ $targetLabel }}
                </span>
                <span class="text-muted me-3">→</span>
                <span class="badge bg-{{ $resultColor }} fs-6">
                    <i class="fa {{ $log->result === 'success' ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                    {{ $resultLabel }}
                </span>
            </div>
            <div class="text-muted small">
                <i class="fa fa-clock-o me-1"></i>
                {{ $log->operated_at ? $log->operated_at->format('Y-m-d H:i:s') : '-' }}
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 左侧：基本信息 + 操作详情 -->
        <div class="col-md-8">
            <!-- 基本信息 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-info-circle me-2"></i>基本信息</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="150">日志ID</td>
                                <td><code>#{{ $log->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">操作类型</td>
                                <td>
                                    <span class="badge bg-{{ $opColor }}">
                                        <i class="fa {{ $opIcon }} me-1"></i>{{ $opLabel }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标类型</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $targetLabel }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标ID</td>
                                <td><code>{{ $log->target_id ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">操作结果</td>
                                <td>
                                    <span class="badge bg-{{ $resultColor }}">
                                        {{ $resultLabel }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">操作时间</td>
                                <td>{{ $log->operated_at ? $log->operated_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">IP地址</td>
                                <td>
                                    @if($log->ip_address)
                                        <code>{{ $log->ip_address }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">User-Agent</td>
                                <td>
                                    @if($log->user_agent)
                                        <span class="small text-muted">{{ $log->user_agent }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 操作详情 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cogs me-2"></i>操作详情</h6>
                </div>
                <div class="card-body">
                    <!-- 关联店铺信息 -->
                    @if($log->target_type === 'shop' && $log->shop)
                        <h6 class="text-muted mb-2">
                            <i class="fa fa-store me-1"></i>关联店铺信息
                        </h6>
                        <table class="table table-sm mb-3">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="150">店铺名称</td>
                                    <td>{{ $log->shop->shop_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">店铺平台</td>
                                    <td><span class="badge bg-light text-dark">{{ $log->shop->platform }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">店铺编码</td>
                                    <td><code>{{ $log->shop->shop_code ?: '-' }}</code></td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <!-- 关联平台账号信息 -->
                    @if($log->target_type === 'platform_account' && $log->platformAccount)
                        <h6 class="text-muted mb-2">
                            <i class="fa fa-cloud me-1"></i>关联平台账号信息
                        </h6>
                        <table class="table table-sm mb-3">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="150">账号名称</td>
                                    <td>{{ $log->platformAccount->account_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">平台名称</td>
                                    <td>{{ $log->platformAccount->platform_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">账号类型</td>
                                    <td>{{ $log->platformAccount->account_type ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <!-- 操作详情描述 -->
                    @if($log->detail)
                        <h6 class="text-muted mb-2">
                            <i class="fa fa-align-left me-1"></i>操作详情
                        </h6>
                        <div class="bg-light p-3 rounded mb-3">
                            {{ $log->detail }}
                        </div>
                    @endif

                    <!-- 错误信息 -->
                    @if($log->error_message)
                        <h6 class="text-muted mb-2">
                            <i class="fa fa-exclamation-triangle me-1 text-danger"></i>错误信息
                        </h6>
                        <div class="alert alert-danger mb-0">
                            <i class="fa fa-times-circle me-2"></i>{{ $log->error_message }}
                        </div>
                    @endif

                    <!-- 扩展数据 -->
                    @if($log->extra_data)
                        <h6 class="text-muted mb-2 mt-3">
                            <i class="fa fa-database me-1"></i>扩展数据
                        </h6>
                        <pre class="bg-light p-3 rounded small mb-0" style="max-height: 300px; overflow-y: auto;">{{ json_encode($log->extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif
                </div>
            </div>
        </div>

        <!-- 右侧：操作人信息 -->
        <div class="col-md-4">
            <!-- 操作人信息 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-user me-2"></i>操作人信息</h6>
                </div>
                <div class="card-body text-center">
                    @if($log->operator)
                        <!-- 操作人头像 -->
                        <div class="user-avatar mx-auto mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.5rem;">
                            {{ mb_substr($log->operator->name, 0, 1) }}
                        </div>
                        <h5 class="mb-1">{{ $log->operator->name }}</h5>
                        <p class="text-muted small mb-3">{{ $log->operator->email }}</p>
                        <table class="table table-sm mb-0 text-start">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="120">用户ID</td>
                                    <td><code>{{ $log->operator->id }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">操作人姓名</td>
                                    <td>{{ $log->operator_name ?? $log->operator->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">创建时间</td>
                                    <td class="small">{{ $log->operator->created_at ? $log->operator->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fa fa-user-circle-o fa-3x mb-2"></i>
                            <div>{{ $log->operator_name ?? '未知操作人' }}</div>
                            <div class="small">操作人信息不可用</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 操作结果摘要 -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-bar-chart me-2"></i>操作结果摘要</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($log->result === 'success')
                            <i class="fa fa-check-circle text-success fa-4x"></i>
                            <h5 class="text-success mt-2">操作成功</h5>
                        @elseif($log->result === 'failed')
                            <i class="fa fa-times-circle text-danger fa-4x"></i>
                            <h5 class="text-danger mt-2">操作失败</h5>
                        @else
                            <i class="fa fa-question-circle text-secondary fa-4x"></i>
                            <h5 class="text-secondary mt-2">未知结果</h5>
                        @endif
                    </div>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="120">租户ID</td>
                                <td><code>{{ $log->tenant_id ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">店铺ID</td>
                                <td><code>{{ $log->shop_id ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">平台账号ID</td>
                                <td><code>{{ $log->platform_account_id ?? '-' }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection