@extends('soho::layouts.app')

@section('title', '告警详情')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-bell me-2"></i>告警详情</h4>
            <a href="{{ route('soho.system.alerts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        @php
            $levelColors = \App\Soho\Models\SystemAlert::getLevelColors();
            $levelIcons = \App\Soho\Models\SystemAlert::getLevelIcons();
            $levelLabels = \App\Soho\Models\SystemAlert::getLevelLabels();
            $sourceLabels = \App\Soho\Models\SystemAlert::getSourceLabels();
            $statusLabels = \App\Soho\Models\SystemAlert::getStatusLabels();
            $statusColors = \App\Soho\Models\SystemAlert::getStatusColors();
        @endphp

        {{-- 告警信息卡片 --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-{{ $levelColors[$alert->level] ?? 'secondary' }} me-2">
                        <i class="fa {{ $levelIcons[$alert->level] ?? 'fa-bell' }} me-1"></i>
                        {{ $levelLabels[$alert->level] ?? $alert->level }}
                    </span>
                    <span class="badge bg-{{ $statusColors[$alert->status] ?? 'secondary' }}">
                        {{ $statusLabels[$alert->status] ?? $alert->status }}
                    </span>
                </div>
                <span class="small text-muted">来源：{{ $sourceLabels[$alert->source] ?? $alert->source }}</span>
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $alert->title }}</h5>
                <p class="card-text">{{ $alert->message }}</p>

                @if($alert->extra_data)
                    <hr>
                    <h6 class="small text-muted">额外数据</h6>
                    <pre class="bg-light p-3 rounded small"><code>{{ json_encode($alert->extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                @endif
            </div>
        </div>

        {{-- 时间线 --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fa fa-clock-o me-2"></i>处理时间线</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fa fa-bell text-danger me-2"></i>告警触发</span>
                        <span class="text-muted">{{ $alert->triggered_at->format('Y-m-d H:i:s') }}</span>
                    </li>
                    @if($alert->acknowledged_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="fa fa-check text-warning me-2"></i>已确认（{{ $alert->acknowledger?->name ?? '未知' }}）</span>
                            <span class="text-muted">{{ $alert->acknowledged_at->format('Y-m-d H:i:s') }}</span>
                        </li>
                    @endif
                    @if($alert->resolved_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="fa fa-wrench text-success me-2"></i>已解决（{{ $alert->resolver?->name ?? '未知' }}）</span>
                            <span class="text-muted">{{ $alert->resolved_at->format('Y-m-d H:i:s') }}</span>
                        </li>
                    @endif
                </ul>

                @if($alert->resolution_note)
                    <div class="alert alert-light border mt-3">
                        <h6 class="small text-muted mb-1"><i class="fa fa-comment me-1"></i>解决备注</h6>
                        <p class="mb-0">{{ $alert->resolution_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 操作按钮 --}}
        @if($alert->status !== 'resolved' && $alert->status !== 'ignored')
        <div class="card">
            <div class="card-body">
                @if($alert->status === 'pending')
                    <form method="POST" action="{{ route('soho.system.alerts.acknowledge', $alert) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-check me-1"></i>确认告警
                        </button>
                    </form>
                @endif

                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal">
                    <i class="fa fa-wrench me-1"></i>解决告警
                </button>

                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#ignoreModal">
                    <i class="fa fa-times me-1"></i>忽略
                </button>
            </div>
        </div>

        {{-- 解决模态框 --}}
        <div class="modal fade" id="resolveModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('soho.system.alerts.resolve', $alert) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">解决告警</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">解决备注 <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="resolution_note" rows="3" required
                                          placeholder="请描述解决措施"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check me-1"></i>确认解决
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 忽略模态框 --}}
        <div class="modal fade" id="ignoreModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('soho.system.alerts.ignore', $alert) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">忽略告警</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">忽略原因（可选）</label>
                                <textarea class="form-control" name="resolution_note" rows="3"
                                          placeholder="说明忽略此告警的原因"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-1"></i>确认忽略
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
