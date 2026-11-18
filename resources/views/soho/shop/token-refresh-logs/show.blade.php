@extends('soho::layouts.app')

@section('title', 'Token刷新记录详情')

@section('content')
<div class="container-fluid">
    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.shop.token-refresh-logs.index') }}">Token刷新记录</a></li>
            <li class="breadcrumb-item active">记录 #{{ $log->id }}</li>
        </ol>
    </nav>

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-refresh me-2"></i>刷新记录详情 #{{ $log->id }}
        </h4>
        <div>
            <a href="{{ route('soho.shop.token-refresh-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>
    </div>

    <!-- 基本信息卡片 -->
    <div class="row">
        <!-- 左侧：基本信息 -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-info-circle me-2"></i>基本信息</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="140">记录ID</td>
                                <td><code>{{ $log->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标类型</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $targetTypeLabels[$log->target_type] ?? $log->target_type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标ID</td>
                                <td><code>{{ $log->target_id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">店铺名称</td>
                                <td>
                                    @if($log->shop)
                                        {{ $log->shop->shop_name }}
                                        <div class="small text-muted">编码：{{ $log->shop->shop_code ?: '-' }}</div>
                                    @elseif($log->platformAccount)
                                        {{ $log->platformAccount->account_name }}
                                        <div class="small text-muted">平台账号</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">平台</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $log->platform }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">租户ID</td>
                                <td><code>{{ $log->tenant_id }}</code></td>
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
                                <td class="text-muted">创建时间</td>
                                <td>
                                    @if($log->created_at)
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 右侧：刷新详情 -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cog me-2"></i>刷新详情</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-5">
                            <span class="badge bg-{{ $resultColors[$log->result] ?? 'secondary' }}" style="font-size: 1rem;">
                                <i class="fa fa-{{ $log->result === 'success' ? 'check' : 'times' }}-circle me-1"></i>
                                {{ $resultLabels[$log->result] ?? $log->result }}
                            </span>
                        </div>
                    </div>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="140">刷新方式</td>
                                <td>
                                    <span class="badge bg-{{ $refreshMethodColors[$log->refresh_method] ?? 'secondary' }}">
                                        {{ $refreshMethodLabels[$log->refresh_method] ?? $log->refresh_method }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">刷新时间</td>
                                <td>
                                    @if($log->refreshed_at)
                                        {{ $log->refreshed_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Token有效期</td>
                                <td>
                                    @if($log->token_lifetime_seconds)
                                        {{ round($log->token_lifetime_seconds / 3600, 1) }} 小时
                                        <div class="small text-muted">({{ round($log->token_lifetime_seconds / 86400, 1) }} 天)</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">刷新前剩余</td>
                                <td>
                                    @if($log->remaining_seconds !== null)
                                        @if($log->remaining_seconds < 86400)
                                            <span class="text-warning">
                                                {{ round($log->remaining_seconds / 3600, 1) }} 小时
                                                <i class="fa fa-exclamation-triangle"></i>
                                            </span>
                                        @else
                                            {{ round($log->remaining_seconds / 86400, 1) }} 天
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @if($log->result === 'failed')
                                <tr>
                                    <td class="text-muted">错误码</td>
                                    <td>
                                        @if($log->error_code)
                                            <code class="text-danger">{{ $log->error_code }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">错误信息</td>
                                    <td>
                                        @if($log->error_message)
                                            <span class="text-danger">{{ $log->error_message }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Token对比卡片 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-exchange me-2"></i>Token过期时间对比</h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <!-- 旧Token -->
                <div class="col-md-6 border-end">
                    <div class="text-muted mb-2"><i class="fa fa-clock-o me-1"></i>旧Token过期时间</div>
                    @if($log->old_token_expires_at)
                        <div class="h4 mb-1">{{ $log->old_token_expires_at->format('Y-m-d') }}</div>
                        <div class="text-muted">{{ $log->old_token_expires_at->format('H:i:s') }}</div>
                        @php
                            $oldDiff = $log->old_token_expires_at->diffForHumans();
                        @endphp
                        <div class="small text-muted mt-1">({{ $oldDiff }})</div>
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>
                <!-- 新Token -->
                <div class="col-md-6">
                    <div class="text-muted mb-2"><i class="fa fa-check-circle me-1"></i>新Token过期时间</div>
                    @if($log->new_token_expires_at)
                        <div class="h4 mb-1 text-success">{{ $log->new_token_expires_at->format('Y-m-d') }}</div>
                        <div class="text-muted">{{ $log->new_token_expires_at->format('H:i:s') }}</div>
                        @php
                            $newDiff = $log->new_token_expires_at->diffForHumans();
                        @endphp
                        <div class="small text-muted mt-1">({{ $newDiff }})</div>
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>
            </div>

            <!-- 差异统计 -->
            @if($log->old_token_expires_at && $log->new_token_expires_at)
                <hr>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="text-muted small">Token有效期</div>
                        <div class="h5 text-primary">
                            {{ round($log->token_lifetime_seconds / 3600, 1) }} 小时
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">刷新时剩余</div>
                        <div class="h5 {{ $log->remaining_seconds < 86400 ? 'text-warning' : 'text-info' }}">
                            @if($log->remaining_seconds !== null)
                                {{ round($log->remaining_seconds / 3600, 1) }} 小时
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">刷新时机</div>
                        <div class="h5">
                            @if($log->remaining_seconds !== null && $log->remaining_seconds < 86400)
                                <span class="badge bg-warning text-dark">紧急刷新</span>
                            @elseif($log->remaining_seconds !== null)
                                <span class="badge bg-success">正常刷新</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- 操作人信息卡片 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-user me-2"></i>操作人信息</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="140">操作人ID</td>
                                <td>
                                    @if($log->operator)
                                        <code>{{ $log->operator->id }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">操作人姓名</td>
                                <td>
                                    @if($log->operator)
                                        {{ $log->operator->name }}
                                    @else
                                        <span class="text-muted">系统自动</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">操作人邮箱</td>
                                <td>
                                    @if($log->operator)
                                        {{ $log->operator->email }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="140">关联店铺</td>
                                <td>
                                    @if($log->shop)
                                        {{ $log->shop->shop_name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">平台账号</td>
                                <td>
                                    @if($log->platformAccount)
                                        {{ $log->platformAccount->account_name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">关联平台</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $log->platform }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 附加数据 -->
    @if($log->extra_data)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-code me-2"></i>附加数据</h6>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ json_encode($log->extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 复制功能
        document.querySelectorAll('[data-clipboard]').forEach(function(el) {
            el.addEventListener('click', function() {
                var text = el.getAttribute('data-clipboard');
                navigator.clipboard.writeText(text).then(function() {
                    var original = el.innerHTML;
                    el.innerHTML = '<i class="fa fa-check"></i> 已复制';
                    setTimeout(function() {
                        el.innerHTML = original;
                    }, 1500);
                });
            });
        });
    });
</script>
@endsection