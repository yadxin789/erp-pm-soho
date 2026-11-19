@extends('soho::layouts.app')

@section('title', '事件重放')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待发布</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">发布失败</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已发布</div><div class="fs-4 fw-semibold text-success">{{ $stats['published'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">重放过的</div><div class="fs-4 fw-semibold">{{ $stats['replayed'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待重放事件</h5>
        <a href="{{ route('soho.integration.connection.status') }}" class="btn btn-outline-secondary btn-sm">连接状态</a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            只允许重放 `pending` / `failed` 状态的事件；重放会把 `publish_status` 置回 `pending`、`retry_count` 加 1，等待事件发布器重新投递。
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="aggregate_type" class="form-select">
                        <option value="">全部聚合类型</option>
                        @foreach($types as $ty)
                            <option value="{{ $ty }}" @selected(request('aggregate_type') === $ty)>{{ $ty }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.event.replay') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        @if($pending->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                没有待重放的事件。
            </div>
        @else
            <form method="POST" action="{{ route('soho.event.replay') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3">
                        <thead>
                            <tr>
                                <th style="width:48px" class="text-center">
                                    <input type="checkbox" class="form-check-input" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)">
                                </th>
                                <th class="font-monospace">事件 ID</th>
                                <th>事件类型</th>
                                <th class="text-center">聚合类型</th>
                                <th class="font-monospace">聚合 ID</th>
                                <th class="text-center">发布状态</th>
                                <th class="text-center">重放次数</th>
                                <th class="text-center">发生时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $ev)
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="form-check-input pick" name="event_ids[]" value="{{ $ev->id }}"></td>
                                    <td class="small text-muted">#{{ $ev->id }}</td>
                                    <td class="small">{{ $ev->event_type }}</td>
                                    <td class="text-center small text-muted">{{ $ev->aggregate_type }}</td>
                                    <td class="small">{{ $ev->aggregate_id }}</td>
                                    <td class="text-center"><span class="badge bg-{{ $ev->publish_status === 'failed' ? 'danger' : 'warning' }}">{{ $ev->publish_status === 'failed' ? '发布失败' : '待发布' }}</span></td>
                                    <td class="text-center">{{ $ev->retry_count }}</td>
                                    <td class="text-center small text-muted">{{ $ev->occurred_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-rotate-right me-1"></i> 重放选中事件</button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">最近模型事件流水</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th class="text-center">事件</th><th class="text-center">模型</th><th class="text-center">模型 ID</th><th class="text-center">操作人</th><th class="text-center">处理时间</th><th class="text-center">发生时间</th></tr></thead>
                <tbody>
                    @forelse($recentEventLogs as $log)
                        <tr>
                            <td class="text-center">{{ $log->event_type }}</td>
                            <td class="text-center small text-muted">{{ $log->model_type }}</td>
                            <td class="text-center">{{ $log->model_id }}</td>
                            <td class="text-center">{{ $log->user_id ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $log->processed_at ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $log->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无事件流水。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
