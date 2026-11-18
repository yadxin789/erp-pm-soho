@extends('soho::layouts.app')

@section('title', '授权异常详情')

@section('content')
<div class="container-fluid">

    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.shop.authorization-exceptions.index') }}">授权异常管理</a></li>
            <li class="breadcrumb-item active">异常详情 #{{ $authorizationException->id }}</li>
        </ol>
    </nav>

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-exclamation-triangle me-2"></i>{{ $authorizationException->title }}
            @php
                $severityColor = $severityColors[$authorizationException->severity] ?? 'secondary';
                $severityLabel = $severityLabels[$authorizationException->severity] ?? '未知';
            @endphp
            <span class="badge bg-{{ $severityColor }} ms-2">{{ $severityLabel }}</span>
        </h4>
        <div>
            <a href="{{ route('soho.shop.authorization-exceptions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>
    </div>

    <div class="row">
        <!-- 左侧：基本信息 + 异常详情 -->
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
                                <td class="text-muted" width="150">异常ID</td>
                                <td><code>{{ $authorizationException->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">标题</td>
                                <td>{{ $authorizationException->title }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">异常类型</td>
                                <td>
                                    @php
                                        $typeColor = $exceptionTypeColors[$authorizationException->exception_type] ?? 'secondary';
                                        $typeIcon = $exceptionTypeIcons[$authorizationException->exception_type] ?? 'fa-question';
                                        $typeLabel = $exceptionTypeLabels[$authorizationException->exception_type] ?? $authorizationException->exception_type;
                                    @endphp
                                    <span class="badge bg-{{ $typeColor }}">
                                        <i class="fa {{ $typeIcon }} me-1"></i>{{ $typeLabel }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">异常级别</td>
                                <td>
                                    <span class="badge bg-{{ $severityColor }}">
                                        {{ $severityLabel }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">目标类型</td>
                                <td>
                                    @php
                                        $targetLabel = $targetTypeLabels[$authorizationException->target_type] ?? '-';
                                    @endphp
                                    <span class="badge bg-light text-dark">{{ $targetLabel }}</span>
                                    @if($authorizationException->target_id)
                                        <code class="ms-1">{{ $authorizationException->target_id }}</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">关联店铺</td>
                                <td>
                                    @if($authorizationException->shop)
                                        <i class="fa fa-store me-1"></i>{{ $authorizationException->shop->shop_name }}
                                        <span class="badge bg-light text-dark ms-1">{{ $authorizationException->shop->platform }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">关联平台账号</td>
                                <td>
                                    @if($authorizationException->platformAccount)
                                        <i class="fa fa-cloud me-1"></i>{{ $authorizationException->platformAccount->account_name }}
                                        <span class="badge bg-light text-dark ms-1">{{ $authorizationException->platformAccount->platform_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">所属平台</td>
                                <td>
                                    @if($authorizationException->platform)
                                        <span class="badge bg-light text-dark">{{ $authorizationException->platform }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">创建时间</td>
                                <td>{{ $authorizationException->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 异常详情 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-bug me-2"></i>异常详情</h6>
                </div>
                <div class="card-body">
                    <!-- 错误代码 -->
                    @if($authorizationException->error_code)
                        <div class="mb-3">
                            <label class="text-muted small d-block">错误代码</label>
                            <code class="d-block p-2 bg-light rounded">{{ $authorizationException->error_code }}</code>
                        </div>
                    @endif

                    <!-- 错误信息 -->
                    @if($authorizationException->error_message)
                        <div class="mb-3">
                            <label class="text-muted small d-block">错误信息</label>
                            <div class="p-2 bg-light rounded">
                                <pre class="mb-0 small">{{ $authorizationException->error_message }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- 详细描述 -->
                    @if($authorizationException->detail)
                        <div class="mb-3">
                            <label class="text-muted small d-block">详细描述</label>
                            <div class="p-2 bg-light rounded">
                                <pre class="mb-0 small">{{ $authorizationException->detail }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- 附加数据 -->
                    @if($authorizationException->extra_data)
                        <div class="mb-0">
                            <label class="text-muted small d-block">附加数据</label>
                            <div class="p-2 bg-light rounded">
                                <pre class="mb-0 small">{{ json_encode($authorizationException->extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- 无异常详情 -->
                    @if(!$authorizationException->error_code && !$authorizationException->error_message && !$authorizationException->detail && !$authorizationException->extra_data)
                        <div class="text-center text-muted py-3">
                            <i class="fa fa-file-alt fa-2x mb-2"></i>
                            <div>暂无详细异常信息</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 发生历史 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-history me-2"></i>发生历史</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="150">首次异常时间</td>
                                <td>
                                    @if($authorizationException->exception_at)
                                        {{ $authorizationException->exception_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">最近异常时间</td>
                                <td>
                                    @if($authorizationException->last_occurred_at)
                                        {{ $authorizationException->last_occurred_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">发生次数</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="fa fa-sync-alt me-1"></i>{{ $authorizationException->occurrence_count ?? 1 }} 次
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">首次至今</td>
                                <td>
                                    @if($authorizationException->exception_at)
                                        {{ $authorizationException->exception_at->diffForHumans() }}
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

        <!-- 右侧：处理信息 + 处理表单 -->
        <div class="col-md-4">

            <!-- 处理信息 -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-wrench me-2"></i>处理信息</h6>
                </div>
                <div class="card-body">
                    <!-- 当前状态 -->
                    <div class="text-center mb-3">
                        @php
                            $statusColor = $statusColors[$authorizationException->status] ?? 'secondary';
                            $statusLabel = $statusLabels[$authorizationException->status] ?? '未知';
                        @endphp
                        <div class="display-6 mb-2">
                            <span class="badge bg-{{ $statusColor }}" style="font-size: 1rem;">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="120">处理方式</td>
                                <td>
                                    @if($authorizationException->handle_method)
                                        @php
                                            $methodLabel = $handleMethodLabels[$authorizationException->handle_method] ?? $authorizationException->handle_method;
                                        @endphp
                                        <span class="badge bg-light text-dark">{{ $methodLabel }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">处理人</td>
                                <td>
                                    @if($authorizationException->handler)
                                        <i class="fa fa-user me-1"></i>{{ $authorizationException->handler->name }}
                                    @elseif($authorizationException->handler_name)
                                        <i class="fa fa-user me-1"></i>{{ $authorizationException->handler_name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">处理时间</td>
                                <td>
                                    @if($authorizationException->handled_at)
                                        {{ $authorizationException->handled_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">尚未处理</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">处理备注</td>
                                <td>
                                    @if($authorizationException->handle_note)
                                        <div class="p-2 bg-light rounded small">{{ $authorizationException->handle_note }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 处理表单（仅待处理/处理中时显示） -->
            @if($authorizationException->needsHandling())
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa fa-check me-2"></i>处理异常</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('soho.shop.authorization-exceptions.handle', $authorizationException) }}">
                            @csrf

                            <!-- 处理方式 -->
                            <div class="mb-3">
                                <label class="form-label">处理方式 <span class="text-danger">*</span></label>
                                <select class="form-select @error('handle_method') is-invalid @enderror"
                                        name="handle_method" required>
                                    <option value="">请选择处理方式</option>
                                    @foreach($handleMethodLabels as $value => $label)
                                        <option value="{{ $value }}" {{ old('handle_method') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('handle_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 处理备注 -->
                            <div class="mb-3">
                                <label class="form-label">处理备注</label>
                                <textarea class="form-control @error('handle_note') is-invalid @enderror"
                                          name="handle_note" rows="3"
                                          placeholder="请输入处理备注说明（选填）">{{ old('handle_note') }}</textarea>
                                @error('handle_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 提交按钮 -->
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-check-circle me-1"></i>提交处理
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection