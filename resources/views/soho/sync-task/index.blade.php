@extends('soho::layouts.app')
@section('title', '同步任务')
@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">筛选条件</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.sync-task.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">店铺</label>
                <select name="shop_id" class="form-select">
                    <option value="">全部店铺</option>
                    @foreach($shops ?? [] as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">任务类型</label>
                <select name="task_type" class="form-select">
                    <option value="">全部类型</option>
                    <option value="order" {{ request('task_type') == 'order' ? 'selected' : '' }}>订单同步</option>
                    <option value="product" {{ request('task_type') == 'product' ? 'selected' : '' }}>商品同步</option>
                    <option value="inventory" {{ request('task_type') == 'inventory' ? 'selected' : '' }}>库存同步</option>
                    <option value="logistics" {{ request('task_type') == 'logistics' ? 'selected' : '' }}>物流同步</option>
                    <option value="aftersale" {{ request('task_type') == 'aftersale' ? 'selected' : '' }}>售后同步</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">状态</label>
                <select name="status" class="form-select">
                    <option value="">全部状态</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>待执行</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>执行中</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>成功</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>失败</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary me-2"><i class="fa fa-search"></i> 查询</button>
                <a href="{{ route('soho.sync-task.index') }}" class="btn btn-secondary">重置</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">同步任务列表</h5>
        <div>
            <form method="POST" action="{{ route('soho.sync-task.batch-execute') }}" class="d-inline" onsubmit="return confirm('确定要批量执行所选任务吗？');">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-play"></i> 批量执行</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>任务ID</th>
                        <th>店铺</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th>执行时间</th>
                        <th>结果</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input task-checkbox" name="task_ids[]" value="{{ $task->id }}">
                            </td>
                            <td><code>#{{ $task->id }}</code></td>
                            <td>{{ $task->shop->name ?? ($task->shop_name ?? '-') }}</td>
                            <td>
                                @php
                                    $typeMap = [
                                        'order' => ['label' => '订单同步', 'class' => 'primary'],
                                        'product' => ['label' => '商品同步', 'class' => 'info'],
                                        'inventory' => ['label' => '库存同步', 'class' => 'warning text-dark'],
                                        'logistics' => ['label' => '物流同步', 'class' => 'secondary'],
                                        'aftersale' => ['label' => '售后同步', 'class' => 'danger'],
                                    ];
                                    $typeInfo = $typeMap[$task->type] ?? ['label' => $task->type, 'class' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => '待执行', 'class' => 'secondary'],
                                        'running' => ['label' => '执行中', 'class' => 'info'],
                                        'success' => ['label' => '成功', 'class' => 'success'],
                                        'failed' => ['label' => '失败', 'class' => 'danger'],
                                    ];
                                    $statusInfo = $statusMap[$task->status] ?? ['label' => $task->status, 'class' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            </td>
                            <td>
                                @if($task->started_at)
                                    {{ $task->started_at->format('Y-m-d H:i:s') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($task->status === 'success')
                                    <span class="text-success"><i class="fa fa-check-circle"></i> 成功</span>
                                @elseif($task->status === 'failed')
                                    <span class="text-danger"><i class="fa fa-times-circle"></i> 失败</span>
                                    @if($task->error_message)
                                        <br><small class="text-muted">{{ Str::limit($task->error_message, 30) }}</small>
                                    @endif
                                @elseif($task->status === 'running')
                                    <span class="text-info"><i class="fa fa-spinner fa-spin"></i> 进行中</span>
                                @else
                                    <span class="text-muted">等待执行</span>
                                @endif
                                @if($task->success_count || $task->fail_count)
                                    <br><small class="text-muted">成功: {{ $task->success_count ?? 0 }} / 失败: {{ $task->fail_count ?? 0 }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if(in_array($task->status, ['pending', 'failed']))
                                        <form method="POST" action="{{ route('soho.sync-task.execute', $task->id) }}" onsubmit="return confirm('确定要执行此任务吗？');">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fa fa-play"></i> 执行
                                            </button>
                                        </form>
                                    @endif
                                    @if($task->status === 'success' || $task->status === 'failed')
                                        <a href="{{ route('soho.sync-task.show', $task->id) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i> 详情
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">暂无同步任务</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tasks->total() > 0)
            <div class="d-flex justify-content-center mt-3">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.task-checkbox').forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
</script>
@endsection