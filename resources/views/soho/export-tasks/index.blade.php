@extends('soho::layouts.app')

@section('title', '导出任务')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">导出任务</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">导出任务列表</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExportModal">
            <i class="fa fa-plus"></i> 创建导出
        </button>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.export_tasks.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索任务编号/类型" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="task_type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="order" @selected(request('task_type') == 'order')>订单导出</option>
                        <option value="product" @selected(request('task_type') == 'product')>商品导出</option>
                        <option value="finance" @selected(request('task_type') == 'finance')>财务导出</option>
                        <option value="inventory" @selected(request('task_type') == 'inventory')>库存导出</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') == 'pending')>待处理</option>
                        <option value="processing" @selected(request('status') == 'processing')>处理中</option>
                        <option value="completed" @selected(request('status') == 'completed')>已完成</option>
                        <option value="failed" @selected(request('status') == 'failed')>失败</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.export_tasks.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>任务编号</th>
                        <th>导出类型</th>
                        <th>状态</th>
                        <th>行数</th>
                        <th>文件大小</th>
                        <th>操作人</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exportTasks as $task)
                    @php
                        // 任务类型中文映射
                        $typeMap = [
                            'order' => '订单导出',
                            'product' => '商品导出',
                            'finance' => '财务导出',
                            'inventory' => '库存导出',
                            'customer' => '客户导出',
                            'report' => '报表导出',
                        ];
                        $typeLabel = $typeMap[$task->task_type] ?? $task->task_type;
                    @endphp
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->task_no }}</td>
                        <td><span class="badge bg-info">{{ $typeLabel }}</span></td>
                        <td>
                            @if($task->status == 'pending')
                                <span class="badge bg-secondary">待处理</span>
                            @elseif($task->status == 'processing')
                                <span class="badge bg-warning text-dark">处理中</span>
                            @elseif($task->status == 'completed')
                                <span class="badge bg-success">已完成</span>
                            @else
                                <span class="badge bg-danger">失败</span>
                            @endif
                        </td>
                        <td>{{ $task->row_count > 0 ? number_format($task->row_count) : '-' }}</td>
                        <td>
                            @if($task->file_size > 0)
                                {{ round($task->file_size / 1024, 2) }} KB
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $task->operator?->name ?? '-' }}</td>
                        <td>{{ $task->created_at ? $task->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            @if($task->status == 'completed')
                                <a href="{{ route('soho.export_tasks.download', $task) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fa fa-download"></i> 下载
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exportTasks->links() }}
    </div>
</div>

<div class="modal fade" id="createExportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('soho.export_tasks.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">创建导出任务</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">导出类型 <span class="text-danger">*</span></label>
                        <select name="task_type" class="form-select" required>
                            <option value="">选择类型</option>
                            <option value="order">订单导出</option>
                            <option value="product">商品导出</option>
                            <option value="finance">财务导出</option>
                            <option value="inventory">库存导出</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">筛选条件</label>
                        <textarea name="filters" class="form-control" rows="3" placeholder="JSON 格式的筛选条件（可选）"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> 创建
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection