@extends('soho::layouts.app')

@section('title', '系统告警')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-bell me-2"></i>系统告警</h4>
        <p class="text-muted mb-0 small">监控系统运行异常，支持确认、解决和忽略操作</p>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body py-3 text-center">
                <div class="text-danger fs-3 fw-bold">{{ $stats['pending'] }}</div>
                <div class="small text-muted">待处理告警</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-dark">
            <div class="card-body py-3 text-center">
                <div class="text-dark fs-3 fw-bold">{{ $stats['critical'] }}</div>
                <div class="small text-muted">未解决致命告警</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body py-3 text-center">
                <div class="text-primary fs-3 fw-bold">{{ $stats['today'] }}</div>
                <div class="small text-muted">今日新增</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body py-3 text-center">
                <div class="text-success fs-3 fw-bold">{{ $stats['resolved_today'] }}</div>
                <div class="small text-muted">今日已解决</div>
            </div>
        </div>
    </div>
</div>

{{-- 筛选栏 --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('soho.system.alerts.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">关键词</label>
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="告警标题 / 内容" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">级别</label>
                <select class="form-select form-select-sm" name="level">
                    <option value="">全部级别</option>
                    @foreach($levels as $value => $label)
                        <option value="{{ $value }}" {{ request('level') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">来源</label>
                <select class="form-select form-select-sm" name="source">
                    <option value="">全部来源</option>
                    @foreach($sources as $value => $label)
                        <option value="{{ $value }}" {{ request('source') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">状态</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">全部状态</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="fa fa-search me-1"></i>筛选
                </button>
                <a href="{{ route('soho.system.alerts.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 告警列表 --}}
<div class="card">
    <div class="card-body p-0">
        @if($alerts->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">暂无告警记录，系统运行正常</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>级别</th>
                            <th>告警标题</th>
                            <th>来源</th>
                            <th>状态</th>
                            <th>触发时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $levelColors = \App\Soho\Models\SystemAlert::getLevelColors();
                            $levelIcons = \App\Soho\Models\SystemAlert::getLevelIcons();
                            $statusColors = \App\Soho\Models\SystemAlert::getStatusColors();
                        @endphp
                        @foreach($alerts as $alert)
                            <tr class="{{ $alert->level === 'critical' && $alert->status === 'pending' ? 'table-danger' : '' }}">
                                <td>
                                    @if($alert->status === 'pending')
                                        <input type="checkbox" name="ids[]" value="{{ $alert->id }}" class="form-check-input alert-checkbox">
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $levelColors[$alert->level] ?? 'secondary' }}">
                                        <i class="fa {{ $levelIcons[$alert->level] ?? 'fa-bell' }} me-1"></i>
                                        {{ $levels[$alert->level] ?? $alert->level }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('soho.system.alerts.show', $alert) }}" class="text-decoration-none fw-semibold">
                                        {{ $alert->title }}
                                    </a>
                                    <div class="small text-muted text-truncate" style="max-width: 400px;">{{ $alert->message }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $sources[$alert->source] ?? $alert->source }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$alert->status] ?? 'secondary' }}">
                                        {{ $statuses[$alert->status] ?? $alert->status }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $alert->triggered_at->format('Y-m-d H:i:s') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('soho.system.alerts.show', $alert) }}" class="btn btn-outline-info" title="详情">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if($alert->status === 'pending')
                                            <form method="POST" action="{{ route('soho.system.alerts.acknowledge', $alert) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="确认">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($alert->status !== 'resolved' && $alert->status !== 'ignored')
                                            <button type="button" class="btn btn-outline-success" title="解决"
                                                    data-bs-toggle="modal" data-bs-target="#resolveModal{{ $alert->id }}">
                                                <i class="fa fa-wrench"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- 解决告警的模态框 --}}
                            @if($alert->status !== 'resolved' && $alert->status !== 'ignored')
                            <div class="modal fade" id="resolveModal{{ $alert->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('soho.system.alerts.resolve', $alert) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">解决告警</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="fw-semibold">{{ $alert->title }}</p>
                                                <p class="small text-muted">{{ $alert->message }}</p>
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
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 批量操作栏 --}}
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-muted" id="selected-count">已选 0 条</span>
                        <form method="POST" action="{{ route('soho.system.alerts.batch-acknowledge') }}" class="d-inline ms-2" id="batch-form">
                            @csrf
                            <input type="hidden" name="ids" id="batch-ids">
                            <button type="submit" class="btn btn-sm btn-outline-warning" id="batch-btn" disabled>
                                <i class="fa fa-check-double me-1"></i>批量确认
                            </button>
                        </form>
                    </div>
                    <div class="small text-muted">
                        共 {{ $alerts->total() }} 条，第 {{ $alerts->currentPage() }} / {{ $alerts->lastPage() }} 页
                    </div>
                    {{ $alerts->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// 全选/反选
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.alert-checkbox').forEach(cb => cb.checked = this.checked);
    updateBatchButton();
});

// 单选更新批量按钮
document.querySelectorAll('.alert-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBatchButton);
});

function updateBatchButton() {
    const checked = document.querySelectorAll('.alert-checkbox:checked');
    const count = checked.length;
    document.getElementById('selected-count').textContent = '已选 ' + count + ' 条';
    document.getElementById('batch-btn').disabled = count === 0;
    document.getElementById('batch-ids').value = JSON.stringify(Array.from(checked).map(cb => parseInt(cb.value)));
}
</script>
@endpush
@endsection
