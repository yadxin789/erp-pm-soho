@extends('soho::layouts.app')

@section('title', '事件日志详情')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">事件日志详情 #{{ $eventLog->id }}</h5>
        <div>
            @if($prevEvent)
                <a href="{{ route('soho.event-log.show', $prevEvent) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-chevron-left"></i> 上一条
                </a>
            @endif
            @if($nextEvent)
                <a href="{{ route('soho.event-log.show', $nextEvent) }}" class="btn btn-sm btn-outline-secondary">
                    下一条 <i class="fa fa-chevron-right"></i>
                </a>
            @endif
            <a href="{{ route('soho.event-log.index') }}" class="btn btn-sm btn-secondary">返回列表</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr><th>事件类型</th><td><span class="badge bg-info">{{ $eventLog->event_type }}</span></td></tr>
                    <tr><th>模型类型</th><td>{{ $eventLog->model_type }}</td></tr>
                    <tr><th>模型ID</th><td>{{ $eventLog->model_id }}</td></tr>
                    <tr><th>触发用户</th><td>{{ $eventLog->user?->name ?? '系统' }}</td></tr>
                    <tr><th>状态</th><td>
                        @if($eventLog->processed_at)
                            <span class="badge bg-success">已处理</span>
                        @else
                            <span class="badge bg-warning">未处理</span>
                        @endif
                    </td></tr>
                    <tr><th>创建时间</th><td>{{ $eventLog->created_at }}</td></tr>
                    @if($eventLog->processed_at)
                    <tr><th>处理时间</th><td>{{ $eventLog->processed_at }}</td></tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>载荷数据</strong></label>
                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ json_encode($eventLog->payload_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
