@extends('soho::layouts.app')

@section('title', '任务重试')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">可重试（失败）</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待执行队列</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">今日完成</div><div class="fs-4 fw-semibold text-success">{{ $stats['completed_today'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">重试策略</div><div class="small text-muted mt-1">重置为 pending，由调度器重新拉起</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">批量重试失败任务</h5>
        <a href="{{ route('soho.sync-task.failed') }}" class="btn btn-outline-secondary btn-sm">失败任务</a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            重试会把任务状态重置为 `pending` 并清空错误信息与结果，任务由同步调度器重新拉起；已成功的任务不受影响。
        </div>

        @if($failed->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                当前没有失败任务可重试。
            </div>
        @else
            <form method="POST" action="{{ route('soho.sync-task.retry') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3">
                        <thead>
                            <tr>
                                <th style="width:48px" class="text-center">
                                    <input type="checkbox" class="form-check-input" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)">
                                </th>
                                <th class="text-center">任务 ID</th>
                                <th>店铺</th>
                                <th class="text-center">类型</th>
                                <th>错误信息</th>
                                <th class="text-center">创建时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failed as $task)
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="form-check-input pick" name="task_ids[]" value="{{ $task->id }}"></td>
                                    <td class="text-center font-monospace small">#{{ $task->id }}</td>
                                    <td>{{ $shops[$task->shop_id]->shop_name ?? '店铺#'.$task->shop_id }}</td>
                                    <td class="text-center">{{ $task->type }}</td>
                                    <td class="small text-danger">{{ $task->error_message ?: '—' }}</td>
                                    <td class="text-center small text-muted">{{ $task->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-rotate-right me-1"></i> 重试选中任务</button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">待执行队列</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th class="text-center">任务 ID</th><th>店铺</th><th class="text-center">类型</th><th class="text-center">入队时间</th></tr></thead>
                <tbody>
                    @forelse($history as $task)
                        <tr>
                            <td class="text-center font-monospace small">#{{ $task->id }}</td>
                            <td>{{ $shops[$task->shop_id]->shop_name ?? '店铺#'.$task->shop_id }}</td>
                            <td class="text-center">{{ $task->type }}</td>
                            <td class="text-center small text-muted">{{ $task->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">队列为空。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
