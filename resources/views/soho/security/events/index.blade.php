@extends('soho::layouts.app')
@section('title', '安全事件')
@section('content')
<div class="mb-3">
    <h4 class="mb-1"><i class="fa fa-shield-alt me-2"></i>安全事件</h4>
    <p class="text-muted mb-0 small">记录和监控系统安全事件</p>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-exclamation-triangle me-2 text-danger"></i>事件列表</h6>
    </div>
    <div class="card-body p-0">
        @if($events->isEmpty())
            <div class="text-center py-5"><i class="fa fa-check-circle fa-3x text-success mb-3"></i><p class="text-muted">暂无安全事件</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>发生时间</th><th>事件类型</th><th>严重程度</th><th>标题</th><th>状态</th><th class="text-end">操作</th></tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                            <tr>
                                <td class="text-muted">{{ $event->id }}</td>
                                <td class="small">{{ $event->occurred_at->format('Y-m-d H:i') }}</td>
                                <td><span class="badge bg-light text-dark">{{ $eventTypeLabels[$event->event_type] ?? $event->event_type }}</span></td>
                                <td><span class="badge bg-{{ $severityColors[$event->severity] ?? 'secondary' }}">{{ $event->severity }}</span></td>
                                <td><div class="fw-semibold small">{{ $event->title }}</div></td>
                                <td><span class="badge bg-{{ $event->status === 'resolved' ? 'success' : ($event->status === 'open' ? 'danger' : 'warning') }}">{{ $statusLabels[$event->status] ?? $event->status }}</span></td>
                                <td class="text-end">
                                    @if($event->status === 'open')
                                        <form method="POST" action="{{ route('soho.security.event.resolve', $event) }}" class="d-inline" onsubmit="return confirm('确定标记为已解决？')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="fa fa-check me-1"></i>解决</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white"><div class="small text-muted">共 {{ $events->count() }} 条记录</div></div>
        @endif
    </div>
</div>
@endsection