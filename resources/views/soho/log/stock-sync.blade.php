@extends('soho::layouts.app')
@section('title', '库存同步日志')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">库存同步日志</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead><tr><th>时间</th><th>商品</th><th>店铺</th><th>原库存</th><th>新库存</th><th>状态</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->systemGoods->name ?? '-' }}</td>
                        <td>{{ $log->shop->name ?? '-' }}</td>
                        <td>{{ $log->old_quantity }}</td>
                        <td>{{ $log->new_quantity }}</td>
                        <td><span class="badge bg-{{ $log->status == 'success' ? 'success' : 'danger' }}">{{ $log->status }}</span></td>
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