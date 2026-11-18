@extends('soho::layouts.app')

@section('title', '授权失败详情')

@section('content')
<div class="container-fluid">
    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.shop.authorization-failures.index') }}">授权失败记录</a></li>
            <li class="breadcrumb-item active">详情 #{{ $failure->id }}</li>
        </ol>
    </nav>

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-exclamation-triangle me-2"></i>授权失败详情 #{{ $failure->id }}
            @php
                $failureColor = $failureTypeColors[$failure->failure_type] ?? 'secondary';
                $statusColor = $statusColors[$failure->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $failureColor }} ms-2">
                {{ $failureTypeLabels[$failure->failure_type] ?? $failure->failure_type }}
            </span>
            <span class="badge bg-{{ $statusColor }}">
                {{ $statusLabels[$failure->status] ?? $failure->status }}
            </span>
        </h4>
        <div>
            <a href="{{ route('soho.shop.authorization-failures.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>
    </div>

    <div class="row">
        <!-- 左侧：基本信息 + 失败详情 + 解决信息 -->
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
                                <td class="text-muted" width="140">记录ID</td>
                                <td><code>{{ $failure->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">店铺名称</td>
                                <td>
                                    @if($failure->shop)
                                        <span class="fw-semibold">{{ $failure->shop->shop_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">平台账号</td>
                                <td>
                                    @if($failure->platformAccount)
                                        <span>{{ $failure->platformAccount->account_name }}</span>
                                        @if($failure->platformAccount->platform_name)
                                            <span class="badge bg-light text-dark ms-1">{{ $failure->platformAccount->platform_name }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">所属平台</td>
                                <td><span class="badge bg-light text-dark">{{ $failure->platform ?: '-' }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标类型</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $targetTypeLabels[$failure->target_type] ?? $failure->target_type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标ID</td>
                                <td><code>{{ $failure->target_id ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">失败时间</td>
                                <td>{{ $failure->failed_at ? $failure->failed_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">创建时间</td>
                                <td>{{ $failure->created_at ? $failure->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 失败详情 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-exclamation-circle me-2"></i>失败详情</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="140">失败类型</td>
                                <td>
                                    <span class="badge bg-{{ $failureColor }}">
                                        {{ $failureTypeLabels[$failure->failure_type] ?? $failure->failure_type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">错误码</td>
                                <td><code>{{ $failure->error_code ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">错误信息</td>
                                <td>
                                    <div class="bg-light p-2 rounded">
                                        {{ $failure->error_message ?: '-' }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">重试次数</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $failure->retry_count ?? 0 }} / {{ $failure->max_retries ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">当前状态</td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $statusLabels[$failure->status] ?? $failure->status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 请求 / 响应数据 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-code me-2"></i>请求 / 响应数据</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-2"><i class="fa fa-paper-plane me-1"></i>请求参数</h6>
                            @if($failure->request_params)
                                <pre class="bg-light p-3 rounded small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($failure->request_params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="fa fa-inbox"></i>
                                    <div class="small">暂无请求参数</div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-2"><i class="fa fa-reply me-1"></i>响应数据</h6>
                            @if($failure->response_data)
                                <pre class="bg-light p-3 rounded small" style="max-height: 300px; overflow-y: auto;">{{ json_encode($failure->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="fa fa-inbox"></i>
                                    <div class="small">暂无响应数据</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧：解决信息 + 解决表单 -->
        <div class="col-md-4">
            <!-- 解决信息 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-check-square me-2"></i>解决信息</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="120">当前状态</td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $statusLabels[$failure->status] ?? $failure->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">解决时间</td>
                                <td>
                                    {{ $failure->resolved_at ? $failure->resolved_at->format('Y-m-d H:i:s') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">解决人</td>
                                <td>
                                    @if($failure->resolver)
                                        {{ $failure->resolver->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">解决备注</td>
                                <td>
                                    @if($failure->resolution_note)
                                        <div class="bg-light p-2 rounded small">
                                            {{ $failure->resolution_note }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 处理表单 -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-wrench me-2"></i>处理操作</h6>
                </div>
                <div class="card-body">
                    @if($failure->status === 'resolved')
                        <div class="text-center py-3">
                            <i class="fa fa-check-circle fa-2x text-success"></i>
                            <div class="mt-2 text-success">该记录已解决</div>
                        </div>
                    @elseif($failure->status === 'ignored')
                        <div class="text-center py-3">
                            <i class="fa fa-ban fa-2x text-secondary"></i>
                            <div class="mt-2 text-muted">该记录已忽略</div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('soho.shop.authorization-failures.resolve', $failure) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">解决备注</label>
                                <textarea name="resolution_note" class="form-control" rows="4" placeholder="请输入解决说明（如：已联系平台客服重新授权）" maxlength="500"></textarea>
                                <div class="form-text">最多 500 个字符</div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-check me-1"></i>标记为已解决
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
