@extends('soho::layouts.app')
@section('title', '同步任务日志')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">同步任务日志</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead><tr><th>时间</th><th>任务类型</th><th>状态</th><th>成功数</th><th>失败数</th><th>耗时(秒)</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->task_type }}</td>
                        <td><span class="badge bg-{{ $log->status == 'success' ? 'success' : 'danger' }}">{{ $log->status }}</span></td>
                        <td>{{ $log->success_count }}</td>
                        <td>{{ $log->fail_count }}</td>
                        <td>{{ $log->duration }}</td>
                    </tr>
                    @empty<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>
@endsection