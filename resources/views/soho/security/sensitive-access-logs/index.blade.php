@extends('soho::layouts.app')
@section('title', '敏感数据访问日志')
@section('content')
<div class="mb-3">
    <h4 class="mb-1"><i class="fa fa-eye me-2"></i>敏感数据访问日志</h4>
    <p class="text-muted mb-0 small">记录对敏感数据（客户、财务等）的访问行为</p>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-list me-2 text-primary"></i>访问记录</h6>
    </div>
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="text-center py-5"><i class="fa fa-inbox fa-3x text-muted mb-3"></i><p class="text-muted">暂无访问记录</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>访问时间</th><th>用户</th><th>数据类型</th><th>操作</th><th>记录标识</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="text-muted">{{ $log->id }}</td>
                                <td class="small">{{ $log->accessed_at->format('Y-m-d H:i') }}</td>
                                <td>@if($log->user)<div class="fw-semibold">{{ $log->user->name }}</div>@endif</td>
                                <td><span class="badge bg-light text-dark">{{ $dataTypeLabels[$log->data_type] ?? $log->data_type }}</span></td>
                                <td><span class="badge bg-info">{{ $actionLabels[$log->action] ?? $log->action }}</span></td>
                                <td class="small">{{ $log->record_identifier ?? '-' }}</td>
                                <td><code class="small">{{ $log->ip_address }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white"><div class="small text-muted">共 {{ $logs->count() }} 条记录</div></div>
        @endif
    </div>
</div>
@endsection