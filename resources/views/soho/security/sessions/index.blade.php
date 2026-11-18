@extends('soho::layouts.app')

@section('title', '会话管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-users me-2"></i>会话管理</h4>
        <p class="text-muted mb-0 small">查看活跃会话，支持强制下线操作</p>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="batchLogout()">
        <i class="fa fa-sign-out me-1"></i>批量下线
    </button>
</div>

{{-- 统计卡片 --}}
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-primary" style="font-size: 1.6rem;">{{ $stats['total'] }}</div>
                <div class="text-muted small">活跃会话</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-success" style="font-size: 1.6rem;">{{ $stats['users'] }}</div>
                <div class="text-muted small">登录用户</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-warning" style="font-size: 1.6rem;">{{ $stats['guests'] }}</div>
                <div class="text-muted small">访客会话</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-info" style="font-size: 1.6rem;">{{ $stats['current'] }}</div>
                <div class="text-muted small">当前会话</div>
            </div>
        </div>
    </div>
</div>

{{-- 列表 --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center">
            <input type="checkbox" id="selectAll" class="form-check-input me-2" onchange="toggleSelectAll()">
            <h6 class="mb-0"><i class="fa fa-list me-2 text-primary"></i>会话列表</h6>
        </div>
    </div>
    <div class="card-body p-0">
        @if($sessions->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无活跃会话</p>
            </div>
        @else
            <form id="batchForm" method="POST" action="{{ route('soho.security.sessions.destroy-batch') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30px;"></th>
                                <th>用户</th>
                                <th>IP地址</th>
                                <th>浏览器</th>
                                <th>操作系统</th>
                                <th>最后活动</th>
                                <th>状态</th>
                                <th class="text-end">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr class="{{ $session->is_current ? 'table-light' : '' }}">
                                    <td>
                                        @if(!$session->is_current)
                                            <input type="checkbox" name="session_ids[]" value="{{ $session->id }}"
                                                   class="form-check-input session-checkbox">
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->user_id)
                                            <div class="fw-semibold">{{ $session->user_name }}</div>
                                            <div class="small text-muted">{{ $session->user_email }}</div>
                                        @else
                                            <span class="text-muted">访客</span>
                                        @endif
                                    </td>
                                    <td><code class="small">{{ $session->ip_address }}</code></td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $session->browser }}</span>
                                    </td>
                                    <td class="small">{{ $session->os }}</td>
                                    <td class="small">
                                        {{ $session->last_activity_at->diffForHumans() }}
                                    </td>
                                    <td>
                                        @if($session->is_current)
                                            <span class="badge bg-primary">当前</span>
                                        @else
                                            <span class="badge bg-success">在线</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$session->is_current)
                                            <form method="POST" action="{{ route('soho.security.sessions.destroy', $session->id) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('确定强制下线该会话？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="强制下线">
                                                    <i class="fa fa-sign-out"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="card-footer bg-white">
                <div class="small text-muted">共 {{ $sessions->count() }} 个会话</div>
            </div>
        @endif
    </div>
</div>

@section('scripts')
<script>
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.session-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function batchLogout() {
    const checkboxes = document.querySelectorAll('.session-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('请选择要下线的会话');
        return;
    }
    if (!confirm('确定下线选中的 ' + checkboxes.length + ' 个会话？')) {
        return;
    }
    document.getElementById('batchForm').submit();
}
</script>
@endsection
@endsection