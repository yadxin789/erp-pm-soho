@extends('soho::layouts.app')

@section('title', '失败任务')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">失败任务</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed_tasks'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">有失败明细的批次</div><div class="fs-4 fw-semibold text-warning">{{ $stats['failed_logs'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">失败记录数</div><div class="fs-4 fw-semibold">{{ $stats['failed_records'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待执行任务</div><div class="fs-4 fw-semibold">{{ $stats['pending_tasks'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">同步任务失败清单</h5>
        <a href="{{ route('soho.sync-task.retry') }}" class="btn btn-outline-secondary btn-sm">任务重试</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        @foreach($types as $ty)
                            <option value="{{ $ty }}" @selected(request('type') === $ty)>{{ $ty }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $s)
                            <option value="{{ $s->id }}" @selected(request('shop_id') == $s->id)>{{ $s->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.sync-task.failed') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">任务 ID</th>
                        <th>店铺</th>
                        <th class="text-center">同步类型</th>
                        <th class="text-center">状态</th>
                        <th>错误信息</th>
                        <th class="text-center">开始</th>
                        <th class="text-center">结束</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td class="text-center font-monospace small">#{{ $task->id }}</td>
                            <td>{{ $shops[$task->shop_id]->shop_name ?? '店铺#'.$task->shop_id }}</td>
                            <td class="text-center">{{ $task->type }}</td>
                            <td class="text-center"><span class="badge bg-{{ $task->status === 'failed' ? 'danger' : 'warning' }}">{{ $task->status }}</span></td>
                            <td class="small text-danger">{{ $task->error_message ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $task->started_at ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $task->completed_at ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                            没有同步失败任务。
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $tasks->links() }}
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">任务批次日志（含失败明细）</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>任务名称</th>
                        <th class="text-center">类型</th>
                        <th class="text-center">平台</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">总数 / 成功 / 失败</th>
                        <th>错误信息</th>
                        <th class="text-center">结束时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->task_name }}</td>
                            <td class="text-center small text-muted">{{ $log->task_type }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($log->platform ?: '—') }}</span></td>
                            <td class="text-center"><span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">{{ $log->status }}</span></td>
                            <td class="text-center">
                                {{ $log->total_count }} / <span class="text-success">{{ $log->success_count }}</span> / <span class="text-danger">{{ $log->fail_count }}</span>
                            </td>
                            <td class="small text-danger">{{ $log->error_message ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $log->finished_at ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无失败批次日志。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
