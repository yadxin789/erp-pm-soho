@extends('pm::layouts.app')

@section('title', '企微消息日志')

@section('content')
<h1 class="mb-4">企微消息日志</h1>

<div class="card">
    <div class="card-body">
        @if($logs->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>时间</th>
                        <th>消息类型</th>
                        <th>标题</th>
                        <th>内容</th>
                        <th>响应码</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->msg_type }}</td>
                        <td>{{ $log->title }}</td>
                        <td class="text-truncate" style="max-width:320px;">{{ $log->content }}</td>
                        <td>{{ $log->response_code }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-comment-slash fa-2x mb-3 d-block"></i>
            暂无消息日志
        </div>
        @endif
    </div>
</div>
@endsection
