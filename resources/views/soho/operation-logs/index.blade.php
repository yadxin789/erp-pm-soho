@extends('soho::layouts.app')
@section('title', '操作日志')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">操作日志</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead><tr><th>时间</th><th>用户</th><th>操作</th><th>描述</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                    @empty<tr><td colspan="5" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>
@endsection