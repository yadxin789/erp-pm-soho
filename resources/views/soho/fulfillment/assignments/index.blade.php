@extends('soho::layouts.app')

@section('title', '待分配作业列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">待分配作业</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-user-plus me-2"></i>待分配作业列表</h4>
    <div>
        <button type="button" class="btn btn-success btn-sm" onclick="autoAssign()">
            <i class="fa fa-magic me-1"></i>智能分配
        </button>
        <button type="button" class="btn btn-primary btn-sm" onclick="batchAssign()">
            <i class="fa fa-users me-1"></i>批量分配
        </button>
    </div>
</div>

{{-- 分配统计卡片 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待拣货</div>
                <div class="fs-3 text-info">{{ $stats['pick_pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待复核</div>
                <div class="fs-3 text-warning">{{ $stats['check_pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待打包</div>
                <div class="fs-3 text-primary">{{ $stats['pack_pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待发货</div>
                <div class="fs-3 text-success">{{ $stats['ship_pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

{{-- 作业分配列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待分配作业</h5>
        <span class="badge bg-info">{{ $assignments->total() }} 条待分配</span>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="作业编号/关联单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部作业类型</option>
                        <option value="pick" @selected(request('type') == 'pick')>拣货任务</option>
                        <option value="check" @selected(request('type') == 'check')>复核任务</option>
                        <option value="pack" @selected(request('type') == 'pack')>打包任务</option>
                        <option value="ship" @selected(request('type') == 'ship')>发货任务</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">全部优先级</option>
                        <option value="urgent" @selected(request('priority') == 'urgent')>紧急</option>
                        <option value="high" @selected(request('priority') == 'high')>高</option>
                        <option value="normal" @selected(request('priority') == 'normal')>普通</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assignee_id" class="form-select">
                        <option value="">分配给</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('assignee_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('soho.fulfillment.assignments.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>作业编号</th>
                        <th>作业类型</th>
                        <th>关联单号</th>
                        <th>仓库</th>
                        <th>优先级</th>
                        <th>建议分配</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $item)
                    <tr>
                        <td><input type="checkbox" name="assignment_ids[]" value="{{ $item->id }}"></td>
                        <td><code>{{ $item->assignment_no }}</code></td>
                        <td>
                            @if($item->type == 'pick')
                                <span class="badge bg-info">拣货</span>
                            @elseif($item->type == 'check')
                                <span class="badge bg-warning text-dark">复核</span>
                            @elseif($item->type == 'pack')
                                <span class="badge bg-primary">打包</span>
                            @elseif($item->type == 'ship')
                                <span class="badge bg-success">发货</span>
                            @else
                                <span class="badge bg-secondary">{{ $item->type }}</span>
                            @endif
                        </td>
                        <td>{{ $item->related_no ?? '-' }}</td>
                        <td>{{ $item->warehouse->name ?? '-' }}</td>
                        <td>
                            @if($item->priority == 'urgent')
                                <span class="badge bg-danger">紧急</span>
                            @elseif($item->priority == 'high')
                                <span class="badge bg-warning text-dark">高</span>
                            @else
                                <span class="badge bg-secondary">普通</span>
                            @endif
                        </td>
                        <td>
                            @if($item->suggested_assignee)
                                <span class="text-muted small">{{ $item->suggested_assignee->name }}</span>
                                <span class="badge bg-success">可用</span>
                            @else
                                <span class="text-danger small">无推荐人员</span>
                            @endif
                        </td>
                        <td>{{ optional($item->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="assignSingle({{ $item->id }})">
                                <i class="fa fa-user-plus"></i> 分配
                            </button>
                            <a href="{{ route('soho.fulfillment.assignments.index', $item) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-check-circle fa-2x mb-2 d-block"></i>
                            暂无待分配作业
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $assignments->links() }}
        </div>
    </div>
</div>

{{-- 分配弹窗 --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignForm" onsubmit="return submitAssign(event)">
                <div class="modal-header">
                    <h5 class="modal-title">分配作业</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assignmentId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">分配给</label>
                        <select id="assigneeSelect" class="form-select" required>
                            <option value="">请选择执行人</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role_name ?? '员工' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">截止时间</label>
                        <input type="datetime-local" id="deadlineInput" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea id="remarkInput" class="form-control" rows="2" placeholder="分配备注（可选）"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">确认分配</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let assignmentIds = [];

document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="assignment_ids[]"]').forEach(cb => cb.checked = this.checked);
});

function assignSingle(id) {
    document.getElementById('assignmentId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

function autoAssign() {
    if (confirm('确认使用智能分配？系统将根据作业类型、仓库位置和员工负载自动分配。')) {
        // 智能分配逻辑
        alert('智能分配已启动！');
    }
}

function batchAssign() {
    const selected = document.querySelectorAll('input[name="assignment_ids[]"]:checked');
    if (selected.length === 0) {
        alert('请至少选择一个作业');
        return;
    }
    assignmentIds = Array.from(selected).map(cb => cb.value);
    document.getElementById('assignmentId').value = assignmentIds.join(',');
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

function submitAssign(e) {
    e.preventDefault();
    const assigneeId = document.getElementById('assigneeSelect').value;
    if (!assigneeId) {
        alert('请选择执行人');
        return false;
    }
    // 提交分配逻辑
    alert('分配成功！');
    bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
    return false;
}
</script>
@endsection