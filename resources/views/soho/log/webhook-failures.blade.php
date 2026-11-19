@extends('soho::layouts.app')

@section('title', 'Webhook 失败记录')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">失败记录</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">未处理完成</div><div class="fs-4 fw-semibold text-warning">{{ $stats['unprocessed'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">接收总量</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">涉及平台</div><div class="fs-4 fw-semibold">{{ $stats['platforms_affected'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">失败事件类型分布</h5></div>
    <div class="card-body">
        @forelse($byType as $t)
            <span class="badge bg-light text-dark border me-2 mb-2">{{ $t->event_type }} <span class="text-danger">{{ $t->cnt }}</span></span>
        @empty
            <span class="text-muted small">暂无失败事件。</span>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">失败明细</h5>
        <a href="{{ route('soho.log.webhook') }}" class="btn btn-outline-secondary btn-sm">Webhook 日志</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" @selected(request('platform') === $p)>{{ strtoupper($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.log.webhook.failures') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">平台</th>
                        <th class="font-monospace">事件 ID</th>
                        <th>事件类型</th>
                        <th class="text-center">状态</th>
                        <th>错误信息</th>
                        <th class="text-center">接收时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform) }}</span></td>
                            <td class="small text-muted">{{ $row->event_id }}</td>
                            <td class="small">{{ $row->event_type }}</td>
                            <td class="text-center"><span class="badge bg-danger">{{ $row->status }}</span></td>
                            <td class="small text-danger">{{ $row->error_message ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                            Webhook 当前没有失败记录。
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
