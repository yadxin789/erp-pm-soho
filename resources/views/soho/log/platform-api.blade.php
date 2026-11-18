@extends('soho::layouts.app')
@section('title', 'API日志')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">API调用日志</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead><tr><th>时间</th><th>店铺</th><th>方法</th><th>订单号</th><th>状态</th><th>耗时(ms)</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->shop->name ?? '-' }}</td>
                        <td>{{ $log->api_method }}</td>
                        <td>{{ $log->order_number ?? '-' }}</td>
                        <td><span class="badge bg-{{ $log->status == 'success' ? 'success' : 'danger' }}">{{ $log->status }}</span></td>
                        <td>{{ $log->response_time ?? '-' }}</td>
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