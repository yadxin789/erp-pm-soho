@extends('soho::layouts.app')

@section('title', '通知消息列表')

@section('content')
<!-- 通知统计卡片 -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">全部消息</h6>
                        <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                    <div class="text-primary">
                        <i class="fa fa-bell fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">未读消息</h6>
                        <h4 class="mb-0 text-warning">{{ $stats['unread'] ?? 0 }}</h4>
                    </div>
                    <div class="text-warning">
                        <i class="fa fa-envelope-o fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">系统通知</h6>
                        <h4 class="mb-0">{{ $stats['system'] ?? 0 }}</h4>
                    </div>
                    <div class="text-info">
                        <i class="fa fa-cog fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">订单提醒</h6>
                        <h4 class="mb-0">{{ $stats['order'] ?? 0 }}</h4>
                    </div>
                    <div class="text-success">
                        <i class="fa fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">通知消息列表</h5>
        <div>
            <a href="{{ route('soho.notifications.read-all') }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-check-circle"></i> 全部标为已读
            </a>
            <a href="{{ route('soho.notifications.delete-read') }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('确定删除所有已读消息？')">
                <i class="fa fa-trash"></i> 删除已读
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索消息标题/内容" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="system" @selected(request('type') == 'system')>系统通知</option>
                        <option value="order" @selected(request('type') == 'order')>订单提醒</option>
                        <option value="inventory" @selected(request('type') == 'inventory')>库存预警</option>
                        <option value="finance" @selected(request('type') == 'finance')>财务通知</option>
                        <option value="platform" @selected(request('type') == 'platform')>平台消息</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">全部优先级</option>
                        <option value="high" @selected(request('priority') == 'high')>高优先级</option>
                        <option value="normal" @selected(request('priority') == 'normal')>普通</option>
                        <option value="low" @selected(request('priority') == 'low')>低优先级</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="is_read" class="form-select">
                        <option value="">全部状态</option>
                        <option value="0" @selected(request('is_read') == '0')>未读</option>
                        <option value="1" @selected(request('is_read') == '1')>已读</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.notifications.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <!-- 通知消息表格 -->
        <form method="POST" action="{{ route('soho.notifications.batch-action') }}" id="batchForm">
            @csrf
            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll(true)">全选</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">取消全选</button>
                <button type="submit" name="action" value="read" class="btn btn-sm btn-success" onclick="return confirmAction('确定将选中的消息标为已读？')">
                    <i class="fa fa-check"></i> 批量标为已读
                </button>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirmAction('确定删除选中的消息？')">
                    <i class="fa fa-trash"></i> 批量删除
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                            </th>
                            <th>类型</th>
                            <th>优先级</th>
                            <th>消息标题</th>
                            <th>发送人</th>
                            <th>发送时间</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                        <tr class="{{ $notification->is_read ? '' : 'table-active' }}">
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $notification->id }}" class="row-checkbox">
                            </td>
                            <td>
                                @php
                                    $typeMap = [
                                        'system' => ['label' => '系统通知', 'class' => 'info', 'icon' => 'fa-cog'],
                                        'order' => ['label' => '订单提醒', 'class' => 'primary', 'icon' => 'fa-shopping-cart'],
                                        'inventory' => ['label' => '库存预警', 'class' => 'warning', 'icon' => 'fa-exclamation-triangle'],
                                        'finance' => ['label' => '财务通知', 'class' => 'success', 'icon' => 'fa-money'],
                                        'platform' => ['label' => '平台消息', 'class' => 'dark', 'icon' => 'fa-globe'],
                                    ];
                                    $typeInfo = $typeMap[$notification->type] ?? ['label' => $notification->type ?? '未知', 'class' => 'secondary', 'icon' => 'fa-bell'];
                                @endphp
                                <span class="badge bg-{{ $typeInfo['class'] }}">
                                    <i class="fa {{ $typeInfo['icon'] }} me-1"></i>{{ $typeInfo['label'] }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $priorityMap = [
                                        'high' => ['label' => '高', 'class' => 'danger'],
                                        'normal' => ['label' => '普通', 'class' => 'secondary'],
                                        'low' => ['label' => '低', 'class' => 'info'],
                                    ];
                                    $priorityInfo = $priorityMap[$notification->priority] ?? ['label' => '普通', 'class' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $priorityInfo['class'] }}">{{ $priorityInfo['label'] }}</span>
                            </td>
                            <td>
                                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#notificationDetailModal" data-notification-id="{{ $notification->id }}">
                                    <strong>{{ $notification->title }}</strong>
                                </a>
                                @if(!$notification->is_read)
                                    <span class="badge bg-danger ms-1">新</span>
                                @endif
                                <br>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($notification->content ?? ''), 50) }}</small>
                            </td>
                            <td>{{ $notification->sender ?? '系统' }}</td>
                            <td>{{ $notification->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <span class="badge bg-{{ $notification->is_read ? 'secondary' : 'warning' }}">
                                    {{ $notification->is_read ? '已读' : '未读' }}
                                </span>
                            </td>
                            <td>
                                @if(!$notification->is_read)
                                    <form method="POST" action="{{ route('soho.notifications.read', $notification) }}" class="d-inline" onsubmit="return markAsReadConfirm()">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fa fa-check"></i> 标为已读
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('soho.notifications.destroy', $notification) }}" class="d-inline" onsubmit="return confirm('确定删除此消息？')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i> 删除
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <p>暂无通知消息</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- 分页 -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                共 {{ $notifications->total() }} 条数据，未读 {{ $stats['unread'] ?? 0 }} 条
            </div>
            {{ $notifications->links() }}
        </div>
    </div>
</div>

<!-- 通知详情弹窗 -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">通知详情</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationDetailBody">
                <!-- 动态加载内容 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="markAsReadBtn" onclick="markAsRead()">标为已读</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 全选/取消全选
    function toggleSelectAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
    }

    function selectAll(checked) {
        document.querySelectorAll('.row-checkbox').forEach(function(cb) {
            cb.checked = checked;
        });
        document.getElementById('selectAll').checked = checked;
    }

    // 批量操作确认
    function confirmAction(message) {
        var checked = document.querySelectorAll('.row-checkbox:checked').length;
        if (checked === 0) {
            alert('请先选择要操作的消息');
            return false;
        }
        return confirm(message);
    }

    // 标记已读确认
    function markAsReadConfirm() {
        return confirm('确定将此通知标记为已读？');
    }

    // 标记已读
    function markAsRead() {
        var id = document.getElementById('notificationDetailModal').dataset.notificationId;
        if (id) {
            fetch('/notifications/' + id + '/read', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(function() {
                location.reload();
            });
        }
    }
</script>
@endsection